<?php
/**
 * Login to Magento from an external directory (same domain!)
 * You will need to set the following value in the Magento backend to make it work:
 *  
 *    System -> General -> Web -> Session Cookie Management -> Cookie path to "/"
 *
 * Author: Lorenzo Sfarra <lorenzosfarra@gmail.com>
 */

//ensure you are getting error output for debug
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

// Do some test here to understand if we are coming from the login page
// with a POST request
if (!isset($_POST['email'])) {
  require_once("./loginform.html");
  exit;
}

// define the Magento base path and import Mage.php
define('MAGENTO', "../shop/app/Mage.php");
require_once(MAGENTO);

// Retrieve info about the login credentials
$email = (string) $_POST["email"];
$password = (string) $_POST["pwd"];

//Mage::setIsDeveloperMode(true);
//Mage::app("default");
$mageRunCode = isset($_SERVER['MAGE_RUN_CODE']) ? $_SERVER['MAGE_RUN_CODE'] : '';
$mageRunType = isset($_SERVER['MAGE_RUN_TYPE']) ? $_SERVER['MAGE_RUN_TYPE'] : 'store';
umask(0);
$app = Mage::app($mageRunCode, $mageRunType);

// Session and login
Mage::getSingleton('core/session', array('name' => 'frontend'));
$session = Mage::getSingleton("customer/session", array('name' => 'frontend'));
$session->start();

// Already logged in?
if ($session->isLoggedIn()) {
  $customer = $session->getCustomer();
  echo $customer->getName() . " already logged in.";
  exit;
}

// Do the login!
try {
  if($session->login($email, $password)) {
    $customer = $session->getCustomer();
    $session->setCustomerAsLoggedIn($customer);
    echo $customer->getName() . " logged in!";
    // $session->isLoggedIn(); is true now on
  } else { 
    echo "Not logged in.";
  };
} catch (Mage_Core_Exception $e) {
  echo "Not logged in, error message: " . $e->getMessage();
}
