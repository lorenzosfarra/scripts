<?php
/**
 * Use of the resource iterator and the walk() function on it for large collections.
 * In this example we delete all the users, just to perform a safe operation!
 *
 * Author: Lorenzo Sfarra <lorenzosfarra@gmail.com>
 */

require_once "app/Mage.php";
Mage::app();

class DeleteAllClients {

  private $lastIterationActions = 0;

  /**
   * Callback method - this is where the user is deleted.
   * @param $args: data related to the currently parsed customer
   */
  function customerCallback($args) {
      $customer = Mage::getModel('customer/customer');
      // Set the data to identify the user, populating the customer model
      $customer->setData($args['row']);
      // Delete the user!
      $customer->delete();
  }

  /**
   * Walk through the collection of all the customers
   * @param the last customer entity ID to consider
   */
  public function deleteAll($limit) {
    echo "\t[deletAll] deleting until customer entity ID $limit\n";
    $customers = Mage::getModel('customer/customer')->getCollection()->addAttributeToFilter('entity_id', array('lt' => $limit));
    // Here we use the resource iterator, and then we walk through the collection
    Mage::getSingleton('core/resource_iterator')->walk($customers->getSelect(), array(array($this, 'customerCallback')));
  }

  /**
   * Get the last customer entity ID to consider
   */
  public function getLastCustomerEntityId() {
    $collection = Mage::getModel('customer/customer')->getCollection()->getLastItem();
    return $collection->getEntityId();
  }

}

// Register that we are in a secure area!
Mage::register('isSecureArea', true);

$itemsForStep = 1000; // Default: we delete 1000 by 1000
$itemsCounter = 1000;

$da = new DeleteAllClients();
$lastCustomerEntityId = $da->getLastCustomerEntityId();

if ($lastCustomerEntityId == 0) {
  echo "No customer to delete..";
} else if ($itemsCounter > $lastCustomerEntityId) {
  echo "Deleting $lastCustomerEntityId all at once...\n";
  // Only one execution!
  $da->deleteAll($lastCustomerEntityId);
} else {
  echo "Start deleting $itemsCounter by $itemsCounter...\n";
  while ($itemsCounter < $lastCustomerEntityId) {
    $da->deleteAll($itemsCounter);
    $itemsCounter += $itemsForStep;
  }
}

echo "Finished!\n";

// Unregister the secure area!
Mage::unregister('isSecureArea');

