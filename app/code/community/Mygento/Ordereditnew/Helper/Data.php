<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Ordereditnew
 * @copyright Copyright © 2016 NKS LLC. (http://www.mygento.ru)
 */
class Mygento_Ordereditnew_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function addLog($text)
    {
        if (Mage::getStoreConfig('ordereditnew/general/debug')) {
            Mage::log($text, null, 'ordereditnew.log', true);
        }
    }
}
