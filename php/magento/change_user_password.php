<?php
/**
 * Author: Lorenzo Sfarra <lorenzosfarra@gmail.com>
 * Website: http://lorenzosfarra.com/
 *
 * Script to change a customer's password.
 *
 * Use it with ?authorized=true .
 * Please provide a better authentication / authorization system!!
 *
 * NOTE: clean and check the $_POST parameters before adding them to your DB
 */

ini_set("display_errors", 1);

// SET UP MAGENTO
define('MAGENTO', realpath(dirname(__FILE__)));
require_once MAGENTO . '/app/Mage.php';
Mage::app();

$errors = false;
$isPostRequest = ($_SERVER['REQUEST_METHOD'] === 'POST');

// Check if it's a POST request
if ($isPostRequest) {
  // Get info about the store ID and website ID
  $storeid = Mage::app()->getStore()->getStoreId();
  $websiteId = Mage::getModel('core/store')->load($storeid)->getWebsiteId();
  try {
    $customer = Mage::getModel("customer/customer");
    $customer->setWebsiteId($websiteId);
    $customer->loadByEmail($_POST['username']);
    $customer->setPassword($_POST['pwd']);
    $customer->save();
    $result = 'Password changed successfully!';
  } catch(Exception $ex) {
    $errors = true;
    $result = $ex->getMessage();
  }
} else { 
// GET

  // TODO: BASIC AUTH || change this!
  if (!isset($_GET['authorized'])) {
    echo "PERMISSION DENIED!";
    exit;
  } 
  if ("true" != $_GET['authorized']) {
    echo "PERMISSION DENIED!";
    exit;
  }
}
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Change user password</title>
    <!-- Import bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" type="text/css" />
    <!-- Callout from: https://gist.github.com/matthiasg/6153853#gistcomment-1182926 -->
    <style>
    /* Base styles (regardless of theme) */
      .bs-callout {
        margin: 20px 0;
        padding: 15px 30px 15px 15px;
        border-left: 5px solid #eee;
      }
      .bs-callout h1,
      .bs-callout h2,
      .bs-callout h3,
      .bs-callout h4,
      .bs-callout h5,
      .bs-callout h6 {
        margin-top: 0;
      }
      
      .bs-callout-danger h1,
      .bs-callout-danger h2,
      .bs-callout-danger h3,
      .bs-callout-danger h4,
      .bs-callout-danger h5,
      .bs-callout-danger h6 {
        color: #B94A48;
      }
      
      .bs-callout-warning h1,
      .bs-callout-warning h2,
      .bs-callout-warning h3,
      .bs-callout-warning h4,
      .bs-callout-warning h5,
      .bs-callout-warning h6 {
        color: #C09853;
      }
      
      .bs-callout-info h1,
      .bs-callout-info h2,
      .bs-callout-info h3,
      .bs-callout-info h4,
      .bs-callout-info h5,
      .bs-callout-info h6 {
        color: #3A87AD;
      }
      
      .bs-callout-success h1,
      .bs-callout-success h2,
      .bs-callout-success h3,
      .bs-callout-success h4,
      .bs-callout-success h5,
      .bs-callout-success h6 {
        color: #3C763D;
      }
      
      .bs-callout p:last-child {
        margin-bottom: 0;
      }
      
      .bs-callout code,
      .bs-callout .highlight {
        background-color: #fff;
      }
      
      /* Themes for different contexts */
      .bs-callout-danger {
        background-color: #fcf2f2;
        border-color: #dFb5b4;
      }
      .bs-callout-warning {
        background-color: #fefbed;
        border-color: #f1e7bc;
      }
      .bs-callout-info {
        background-color: #f0f7fd;
        border-color: #d0e3f0;
      }
      .bs-callout-success {
        background-color: #dff0d8;
        border-color: #d6e9c6;
      }
    </style>
  </head>

  <body>
    <div class="container">
      <div class="row">
        <?php // Check for errors or messages
        if ($errors) : ?>
          <div class="bs-callout bs-callout-danger">
            <h4>Error</h4>
            <p><?php echo $result; ?></p>
          </div>
        <?php else : ?>
          <?php if ($isPostRequest) : ?>
            <div class="bs-callout bs-callout-success">
              <h4>Success</h4>
              <p><?php echo $result; ?></p>
            </div>
          <?php endif; ?>
        <?php endif; ?>
        <h1 class="bd-title">Change user password</h1>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
          <fieldset class="form-group">
            <label for="username">Username:</label>
            <input id="username" name="username" class="form-control" placeholder="Enter username" type="email"/> 
          </fieldset>
          <fieldset class="form-group">
            <label for="pwd">Nuova password:</label>
            <input id="pwd" name="pwd" class="form-control" placeholder="Enter password" type="password"/> 
            <small class="text-muted">Alpha-numeric characters</small>
          </fieldset>
          <button class="btn btn-primary" type="submit">Change password</button>
        </form>
      </div>
    </div>
    <!-- Import jQuery, bootstrap JS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script> 
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" type="text/javascript"> </script>
  </body>
</html>
