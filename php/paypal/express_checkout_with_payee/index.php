<!DOCTYPE html>
<html>
<!-- 
Index for the test.
Please edit the CREATE_URL and EXECUTE_URL.
-->
<head>
  <title>PayPal Express Checkout Payee test</title>
  <!-- Meta stuff -->
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="author" content="Lorenzo Sfarra"/>
  <meta name="contact" content="Lorenzo Sfarra, lorenzosfarra@gmail.com">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Scripts and styles -->
  <script src="https://www.paypalobjects.com/api/checkout.js"></script>
  <link rel="stylesheet" href="css/main.css"/>

</head>
<body>

  <h1>PayPal Express Checkout</h1>
  <h3>Payee test</h3>

  <div id="paypal-button"></div>

  <hr id="spacing"/>
  
  <div id="payment-complete">
      Payment received!
  </div>

  <pre id="payment-details"></pre>
  <script>
        <!--
        paypal.Button.render({
            env: 'sandbox',
            // Show the buyer a 'Pay Now' button in the checkout flow
            commit: true,

            style: {
              color: 'silver'
            },

            // payment() is called when the button is clicked
            payment: function() {

                // Set up a url on your server to create the payment
                const CREATE_URL = 'http://something.com/create.php';

                // Make a call to your server to set up the payment
                return paypal.request.post(CREATE_URL)
                    .then(function(res) {
                        return res.id;
                    });
            },

            // onAuthorize() is called when the buyer approves the payment
            onAuthorize: function(data, actions) {
                // Set up a url on your server to execute the payment
                const EXECUTE_URL = 'http://something.com/execute.php';

                // Set up the data you need to pass to your server
                var data = {
                    paymentID: data.paymentID,
                    payerID: data.payerID
                };

                // Make a call to your server to execute the payment
                return paypal.request.post(EXECUTE_URL, data)
                    .then(function (res) {
                        console.log(res);
                        document.getElementById('payment-complete').style.display = "block";
                        document.getElementById('paypal-button').style.display = "none";
                        document.getElementById('payment-details').innerHTML = JSON.stringify(res);
                        document.getElementById('payment-details').style.display = "block";
                    });
            }

        }, '#paypal-button');
        -->
  </script>
</body>
</html>
