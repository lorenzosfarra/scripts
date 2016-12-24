<?php

/**
 * Rules price - catalog / categories
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2016 Lorenzo Sfarra (<lorenzosfarra@gmail.com> - http://lorenzosfarra.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Lorenzo_Rulespriceapi_Model_Product_Api extends Mage_Api_Model_Resource_Abstract
{
    
  public function items($categoryId, $storeId)
  {
    $arr_products = array();
    $category = Mage::getModel('catalog/category')
                  ->setStoreId($storeId)
                  ->load($categoryId);
    $products = $category->getProductCollection()
                  ->addAttributeToSelect('name')
                  ->addAttributeToSelect('price');
    foreach ($products as $product) {
      $wId = Mage::app()->getStore($storeId)->getWebsiteId();
      // Better check for customer group ID..
      if ($product->hasCustomerGroupId()) {
        $gId = $product->getCustomerGroupId();
      } else {
        $gId = 1;
      }
      $pId = $product->getId();
      $today = date('Y-m-d');
      $rulePrice = Mage::getResourceModel('catalogrule/rule')
                          ->getRulePrice($today, $wId, $gId, $pId);
      if ($rulePrice === false) {
        $rulePrice = -1;
      }
      $prod = Array(
        'rulesPrice' => $rulePrice,
        'price'      => $product->getPrice(),
        'entity_id'  => $product->getId(),
        'name'       => $product->getName()
      );
      $arr_products[] = $prod;
    }
    return $arr_products;
  }
}
