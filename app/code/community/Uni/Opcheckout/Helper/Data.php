<?php

require_once 'Mage/Checkout/Helper/Data.php';

class Uni_Opcheckout_Helper_Data extends Mage_Checkout_Helper_Data {

    const COMPATIBILITY_TYPE1 = 1; //for above
    const COMPATIBILITY_TYPE2 = 2; //for 1.4.0.0 and 1.4.0.1

    public function isActive() {
        return Mage::getStoreConfigFlag('opcheckout/general/enabled');
    }

    public function isOrderCommentEnabled() {
        Mage::helper('unicommon')->c($this);
        return Mage::getStoreConfigFlag('opcheckout/order/opcheckout_order_comment') ? true : false;
    }

    public function isCouponDiscountEnabled() {
        return Mage::getStoreConfigFlag('opcheckout/order/opcheckout_order_couponcode') ? true : false;
    }

    public function isOrderDeliveryEnabled() {
        return Mage::getStoreConfigFlag('opcheckout/order/opcheckout_order_deliverydate') ? true : false;
    }

    public function setOrderCommentCompatible1($observer) {
        if ($this->getCompatibility() != self::COMPATIBILITY_TYPE1) {
            return;
        }
        $quote = $observer->getEvent()->getQuote();
        if ($this->isOrderCommentEnabled()) {
            $orderComment = trim($this->_getRequest()->getPost('order_comment'));
            if ($orderComment != "") {
                $observer->getEvent()->getOrder()->setOrderComment($orderComment);
            } else {
                $observer->getEvent()->getOrder()->setOrderComment(Mage::getSingleton('checkout/session')->getOpcheckoutOrderComment());
                Mage::getSingleton('checkout/session')->unsOpcheckoutOrderComment();
            }
        }
        $this->setGiftMessage($observer);
        $this->saveShippingArrivalDate($observer);
        $invetoryObserver = Mage::getModel('cataloginventory/observer');
        if (!$quote->getInventoryProcessed()) {
            $invetoryObserver->subtractQuoteInventory($observer);
            $invetoryObserver->reindexQuoteInventory($observer);
        }
    }

    public function setOrderCommentCompatible2($observer) {
        if ($this->getCompatibility() != self::COMPATIBILITY_TYPE2) {
            return;
        }
        if ($this->isOrderCommentEnabled()) {
            $orderComment = trim($this->_getRequest()->getPost('order_comment'));
            if ($orderComment == "") {
                $orderComment = Mage::getSingleton('checkout/session')->getOpcheckoutOrderComment();
                Mage::getSingleton('checkout/session')->unsOpcheckoutOrderComment();
            }
            $order = $observer->getEvent()->getOrder();
            if ($order && ($orderComment != "")) {
                if ($order->getId()) {
                    $resource = Mage::getSingleton('core/resource');
                    $sales_order = $resource->getTableName('sales_order');
                    $resource->getConnection('core_write')->update($sales_order, array('order_comment' => $orderComment), array('entity_id = ?' => $order->getId()));
                }
            }
        }
        $this->setGiftMessage($observer);
        $this->saveShippingArrivalDate($observer);
    }

    public function isAllowedNewsletterSubscription() {
        $customerSession = Mage::getSingleton('customer/session');
        if (defined('Mage_Newsletter_Model_Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG') && Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG) != 1 && !$customerSession->isLoggedIn()) {
            return false;
        } else {
            if ($customerSession->isLoggedIn()) {
                $subscriber = Mage::getModel('newsletter/subscriber')->loadByCustomer($customerSession->getCustomer());
                return !$subscriber->isSubscribed() && Mage::getStoreConfig('opcheckout/order/opcheckout_newsletter_subscribe');
            }
            return Mage::getStoreConfig('opcheckout/order/opcheckout_newsletter_subscribe');
        }
    }

    public function isNewsletterSubscriptionChecked() {
        return (boolean) Mage::getStoreConfig('opcheckout/order/newsletter_checked_bydefault');
    }

    public function getCompatibility() {
        $version = Mage::getVersion();
        if (in_array($version, array('1.4.0.0', '1.4.0.1'))) {
            return self::COMPATIBILITY_TYPE2;
        } else {
            return self::COMPATIBILITY_TYPE1;
        }
    }

    /*
     *  Set Order Delivery Date..
     */

    public function saveShippingArrivalDate($observer) {
        $DDate = $this->_getRequest()->getPost('shipping_arrival_date');
        if ($this->isOrderDeliveryEnabled()) {
            if ($DDate == "") {
                $DDate = Mage::getSingleton('checkout/session')->getShippingArrivalDate();
                Mage::getSingleton('checkout/session')->unsShippingArrivalDate();
            }
            $order = $observer->getEvent()->getOrder();
            $desiredArrivalDate = $DDate;
            if (isset($desiredArrivalDate) && !empty($desiredArrivalDate)) {
                if ($this->getCompatibility() == self::COMPATIBILITY_TYPE2) {
                    $resource = Mage::getSingleton('core/resource');
                    $sales_order = $resource->getTableName('sales_order');
                    $resource->getConnection('core_write')->update($sales_order, array('shipping_arrival_date' => $desiredArrivalDate), array('entity_id = ?' => $order->getId()));
                } else {
                    $order->setShippingArrivalDate($desiredArrivalDate);
                }
            }
        }
    }

    public function getFormatedDeliveryDateToSave($date = null) {
        if (empty($date) || $date == null || $date == '0000-00-00 00:00:00') {
            return null;
        }

        $timestamp = null;
        try {
            //TODO: add Better Date Validation
            $timestamp = strtotime($date);
            $dateArray = explode("-", $date);
            if (count($dateArray) != 3) {
                //invalid date
                return null;
            }
            $formatedDate = date('Y-m-d H:i:s', strtotime($date));
        } catch (Exception $e) {
            //TODO: email error 
            //return null if not converted ok
            return null;
        }

        return $formatedDate;
    }

    /*
     * @ Gift Messages for Order and Item
     */

    public function setGiftMessage($observer) {
        $message = $this->_getRequest()->getPost('giftmessage');
        if (empty($message)) {
            $message = Mage::getSingleton('checkout/session')->getData('opgiftmessage');
            Mage::getSingleton('checkout/session')->unsetData('opgiftmessage');
        }
        $quotemsg = array();
        $order = $observer->getEvent()->getOrder();
        if(is_array($message)) {
            foreach ($message as $msgid => $msg):
                if ($msg['type'] == 'quote') {
                    $m = $msg['message'];
                    $from = $msg['from'];
                    $to = $msg['to'];
                    $giftMessage = Mage::getModel('giftmessage/message');
                    if ($m !== '') {
                        $giftMessage->setSender($from);
                        $giftMessage->setRecipient($to);
                        $giftMessage->setMessage($m);
                        $giftMessage->save();
                    }
                    $order->setGiftMessageId($giftMessage->getId());
                } else if ($msg['type'] == 'quote_item') {
                    $m = $msg['message'];
                    $from = $msg['from'];
                    $to = $msg['to'];
                    $giftMessage = Mage::getModel('giftmessage/message');
                    if ($m !== '') {
                        $giftMessage->setSender($from);
                        $giftMessage->setRecipient($to);
                        $giftMessage->setMessage($m);
                        $giftMessage->save();
                    }
                    $quotemsg[$msgid] = $giftMessage->getId();
                    if ($this->getCompatibility() == self::COMPATIBILITY_TYPE2) {
                        $resource = Mage::getSingleton('core/resource');
                        $sales_order = $resource->getTableName('sales_flat_order_item');
                        $resource->getConnection('core_write')->update($sales_order, array('gift_message_id' => $giftMessage->getId()), array('order_id= ?' => $order->getId()));
                    }
                }
            endforeach;
            $items = $order->getAllItems();
            foreach ($items as $itemId => $item) {
                if (array_key_exists($item['quote_item_id'], $quotemsg)) {
                    $item->setGiftMessageId($quotemsg[$item['quote_item_id']]);
                }
            }
        }
    }

}