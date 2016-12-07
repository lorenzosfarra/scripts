<?php
/**
 * Author: Lorenzo Sfarra <lorenzosfarra@gmail.com>
 * Website: http://lorenzosfarra.com/
 *
 * Script to check if a user is online
 *
 * Use it with ?authorized=true&email=<user_to_check@example.com> .
 * Please provide a better authentication / authorization system!!
 *
 */

ini_set("display_errors", 1);

// SET UP MAGENTO
define('MAGENTO', realpath(dirname(__FILE__)));
require_once MAGENTO . '/app/Mage.php';
Mage::app();

$errors = false;
$online = false;

// TODO: BASIC AUTH || change this!
if (!isset($_GET['authorized']) || ("true" != $_GET['authorized'])) {
  $errors = true;
  $result = "PERMISSION DENIED!";
} else {
  // Get info about the store ID and website ID
  $storeid = Mage::app()->getStore()->getStoreId();
  $websiteId = Mage::getModel('core/store')->load($storeid)->getWebsiteId();
  $customer = Mage::getModel("customer/customer");
  $customer->setWebsiteId($websiteId);
  $customer->loadByEmail($_GET['email']);
  $id = $customer->getId();
  
  if (!$id) {
    $errors = true;
    $result = "CUSTOMER NOT FOUND!";
  } else {
    $customerLog = Mage::getModel('log/customer')->loadByCustomer($id);
    $online = !($customerLog->getLogoutAt() ||
        strtotime(now())-strtotime($customerLog->getLastVisitAt())>Mage_Log_Model_Visitor::getOnlineMinutesInterval()*60);
  }
} 
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Check if a user is online</title>
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
      .bs-callout h4 {
        margin-top: 0;
      }
      
      .bs-callout-danger h4 {
        color: #B94A48;
      }
      
      .bs-callout-warning h4 {
        color: #C09853;
      }
      
      .bs-callout-success h4 {
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
          <?php if ($online) : ?>
            <div class="bs-callout bs-callout-success">
              <h4>ONLINE</h4>
              <p><?php echo $customer->getName() . " is online."; ?></p>
            </div>
          <?php else: ?>
            <div class="bs-callout bs-callout-warning">
              <h4>OFFLINE</h4>
              <p><?php echo $customer->getName() . " is offline."; ?></p>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </body>
</html>
