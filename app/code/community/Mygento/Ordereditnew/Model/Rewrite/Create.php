<?php

/**
 *
 *
 * @category Mygento
 * @package Mygento_Ordereditnew
 * @copyright Copyright © 2016 NKS LLC. (http://www.mygento.ru)
 */
class Mygento_Ordereditnew_Model_Rewrite_Create extends Mage_Adminhtml_Model_Sales_Order_Create
{

  /**
     * Create new order
     *
     * @return Mage_Sales_Model_Order
     */
    public function createOrder()
    {
        $this->_prepareCustomer();
        $this->_validate();
        $quote = $this->getQuote();
        $this->_prepareQuoteItems();

        if (! $quote->getCustomer()->getId() || ! $quote->getCustomer()->isInStore($this->getSession()->getStore())) {
            $quote->getCustomer()->sendNewAccountEmail('registered', '', $quote->getStoreId());
        }
        $service = Mage::getModel('sales/service_quote', $quote);
        if ($this->getSession()->getOrder()->getId()) {
            $oldOrder = $this->getSession()->getOrder();
            $originalId = $oldOrder->getOriginalIncrementId();
            if (!$originalId) {
                $originalId = $oldOrder->getIncrementId();
            }

            // Generate new ID for order
            $quote->reserveOrderId();

            $orderData = array(
                'original_increment_id'     => $originalId,
                'relation_parent_id'        => $oldOrder->getId(),
                'relation_parent_real_id'   => $oldOrder->getIncrementId(),
                'edit_increment'            => $oldOrder->getEditIncrement()+1,
                // our modification
                'increment_id'              => $quote->getReservedOrderId(),
            );
            $quote->setReservedOrderId($orderData['increment_id']);
            $service->setOrderData($orderData);
        }

        $order = $service->submit();
        if (!$quote->getCustomer()->getId() || !$quote->getCustomer()->isInStore($this->getSession()->getStore())) {
            $quote->getCustomer()->setCreatedAt($order->getCreatedAt());
            $quote->getCustomer()->save();
        }
        if ($this->getSession()->getOrder()->getId()) {
            $oldOrder = $this->getSession()->getOrder();

            $this->getSession()->getOrder()->setRelationChildId($order->getId());
            $this->getSession()->getOrder()->setRelationChildRealId($order->getIncrementId());
            $this->getSession()->getOrder()->cancel()
                ->save();
            $order->save();
        }
        if ($this->getSendConfirmation()) {
            $order->sendNewOrderEmail();
        }

        Mage::dispatchEvent('checkout_submit_all_after', array('order' => $order, 'quote' => $quote));

        return $order;
    }
}
