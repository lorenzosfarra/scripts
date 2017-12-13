<?php
/**
 * Author: Lorenzo Sfarra <lorenzosfarra@gmail.com>
 * Website: http://lorenzosfarra.com/
 *
 * Class to handle the various aspect of the PayPal Payment cycle.
 * It creates and set up the various objects (Payment, Transaction)
 * needed to create and execute the payment.
 *
 * PLEASE EDIT the $payPalCredential, $testValues (payee, amount and urls) properties.
 **/

require __DIR__ . '/lib/PayPal-PHP-SDK/autoload.php';

class Payment
{

    // Keep these stuff private and load it.
    // Edit with your credentials.
    // First element is the client ID, the second one is the secret
    private $payPalCredential = [
        'clientId' => 'CLIENT_ID',
        'secret' =>   'SECRET' 
    ];

    // Edit with your testing values
    /**
     * @var string[]
     */
    private $testValues = [
        'payee' => 'payee_email@something.com',
        'intent' => 'sale',
        'transaction' => [
            'amount' => '35.00',
            'currency' => 'EUR',
        ],
        'urls' => [
            'return' => 'http://something.com/return.php',
            'cancel' => 'http://something.com/cancel.php',
        ],
    ];

    // The PayPal context
    /**
     * @var \PayPal\Rest\ApiContext
     */
    private $payPalContext;

    /**
     * Constructor
     * Set up the PayPal context
     */
    public function __construct()
    {
        $this->apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                $this->payPalCredential['clientId'],
                $this->payPalCredential['secret']
            )
        );
    }

    /**
     * Create the amount object.
     * Here an example amount of 1 Euro returned
     * @return \PayPal\Api\Amount
     */
    private function setUpAmount()
    {
        $amount = new \PayPal\Api\Amount();
        $amount->setTotal($this->testValues['transaction']['amount']);
        $amount->setCurrency($this->testValues['transaction']['currency']);
        return $amount;
    }

    /**
     * Create a Payee object
     * @return \PayPal\Api\Payee
     */
    private function getPayee()
    {
        $payee = new \PayPal\Api\Payee();
        $payee->setEmail($this->testValues['payee']);
        return $payee;
    }

    /**
     * Set up a transaction object.
     * @param \PayPal\Api\Amount the amount to be payed
     * @param \PayPal\Api\Payee the user to be payed
     * @return \PayPal\Api\Transaction
     */
    private function setUpTransaction($amount, $payee)
    {
        $transaction = new \PayPal\Api\Transaction();
        $transaction->setAmount($amount);
        $transaction->setPayee($payee);
        $transaction->setDescription("Testing transaction");
        $transaction->setNoteToPayee("Note to payee: testing transaction");
        return $transaction;
    }

    /**
     * Get the urls for Return and Cancel action
     */
    private function getCallbackUrls()
    {
        $redirectUrls = new \PayPal\Api\RedirectUrls();
        $redirectUrls->setReturnUrl($this->testValues['urls']['return'])
            ->setCancelUrl($this->testValues['urls']['cancel']);
        return $redirectUrls;
    }

    /**
     * Set up a payment object
     * @param \PayPal\Api\Payer the payer
     * @param \PayPal\Api\Transaction the transaction to be done
     * @param \PayPal\Api\RedirectUrls the callbacks URLs
     * @return \PayPal\Api\Payment
     */
    private function setUpPayment($payer, $transaction, $redirectUrls)
    {
        $payment = new \PayPal\Api\Payment();
        $payment->setIntent($this->testValues['intent'])
            ->setPayer($payer)
            ->setTransactions(array($transaction))
            ->setRedirectUrls($redirectUrls);
        return $payment;
    }

    /**
     * Get a payment object by a payment ID
     * @param string the payment ID
     * @return \PayPal\Api\Payment
     */
    private function getPaymentById($paymentId)
    {
        $payment = \PayPal\Api\Payment::get($paymentId, $this->apiContext);
        return $payment;
    }

    /**
     * Method that will serve the PayPal payment function from JS
     */
    public function create()
    {
        $apiContext = $this->apiContext;
        // Setting up the payer
        $payer = new \PayPal\Api\Payer();
        $payer->setPaymentMethod('paypal');

        // Set up the Amount
        $amount = $this->setUpAmount();

        // Get the payee object
        $payee = $this->getPayee();

        // Set up the transaction object
        $transaction = $this->setUpTransaction($amount, $payee);

        // Get the callbacks URLs
        $redirectUrls = $this->getCallbackUrls();

        // Set up the payment object
        $payment = $this->setUpPayment($payer, $transaction, $redirectUrls);


        try {
            // It will have a JSON response
            header('Content-Type: application/json');

            // Create the payment!
            $payment->create($apiContext);
            $data = [
                'id' => $payment->getId(),
                'state' => $payment->getState(),
            ];
            echo json_encode($data);
        } catch (\PayPal\Exception\PayPalConnectionException $ex) {
            // Details for debug:
            echo $ex->getData();
        }
    }

    /**
     * Create an execution.
     * @param string the payer ID
     * @return \PayPal\Api\PaymentExecution
     */
    private function createExecution($payerId)
    {
        $execution = new \PayPal\Api\PaymentExecution();
        $execution->setPayerId($payerId);
        return $execution;
    }

    /**
     * Execute the payment
     * P.S.: here no validation is done on the POST values.
     */
    public function execute()
    {
        $apiContext = $this->apiContext;

        // Retrieve values from the POST object, please validate!
        $paymentId = $_POST['paymentID'];
        $payerId = $_POST['payerID'];

        // Get the payment object by its ID
        $payment = $this->getPaymentById($paymentId);

        // Create an execution for the payer ID
        $execution = $this->createExecution($payerId);

        try {
            // It will have a JSON response
            header('Content-Type: application/json');

            $result = $payment->execute($execution, $apiContext);
            try {
                $payment = $this->getPaymentById($paymentId);
                echo json_encode([
                    'status' => 'OK',
                    'paymentId' => $paymentId,
                ]);
            } catch (Exception $ex) {
                header($_SERVER["SERVER_PROTOCOL"] . ' 400 Payment Execution Failed');
                error_log("failed to get updated payment: " . print_r($ex, true));
                throw $ex;
            }
        } catch (Exception $ex) {
            error_log("failed to execute payment: " . print_r($ex, true));
            header($_SERVER["SERVER_PROTOCOL"] . ' 400 Payment Execution Failed');
        }

    }
}
