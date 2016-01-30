<?php
/**
 * Add an additional attribute in a Magento order.
 * The example is about the platform from which the order was placed.
 *
 * Example usage: $order->setOrderPlatform("iOS");
 *
 * Author: Lorenzo Sfarra <lorenzosfarra@gmail.com>
 */

require_once('app/Mage.php');
Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));
$installer = new Mage_Sales_Model_Mysql4_Setup;

// The default platform is desktop
$attribute_id = "order_platform";
$attribute_label = "Order platform";
$default_value = "desktop";

$attribute  = array(
        'type'          => 'varchar',
        'backend_type'  => 'text',
        'frontend_input' => 'text',
        'is_user_defined' => true,
        'label'         => $attribute_label, 
        'visible'       => true,
        'required'      => false,
        'user_defined'  => false,   
        'searchable'    => false,
        'filterable'    => false,
        'comparable'    => false,
        'default'       => $default_value
);

$installer->addAttribute('order', $attribute_id, $attribute);
$installer->endSetup();

echo "Attribute $attribute_id added successfully.";

?>
