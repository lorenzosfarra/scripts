<?php
/**
 * Author: Lorenzo Sfarra <lorenzosfarra@gmail.com>
 * Website: http://lorenzosfarra.com/
 *
 * Class to hold the different requests coming from the client.
 * Basically the create and execute requests.
 **/

require_once __DIR__ . "/Payment.php";

/**
 * Handle the create() and execute() requests
 */
class Process {
  /**
   * The payment object
   * @var Payment
   */
  private $payment;

  public function __construct()
  {
    $this->payment = new \Payment();
  }

  public function create()
  {
    $this->payment->create();
  }

  public function execute()
  {
    $this->payment->execute();
  }
}
