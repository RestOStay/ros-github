<?php

class Zaakpay_Model_Transact extends Mage_Payment_Model_Method_Abstract 
{

    protected $_code = 'zaakpay';

    /* protected $_canAuthorize            = true; */
    protected $_canCapture              = true;
    protected $_isInitializeNeeded      = true;
    protected $_canUseInternal          = false;
    protected $_canUseForMultishipping  = false;
    protected $_canVoid                 = true;
    protected $_canRefund               = true;

    /**
     * Return Order place redirect url
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('zaakpay/transact/redirect', array('_secure' => true));
    }

    /**
     * Url to which the post form will be submitted
     * and the user will be redirected
     * @param String Url
     */
    public function getZaakpayTransactAction() 
    {
        return 'https://api.zaakpay.com/transact';
    }

    /**
     * Url for calling the update api
     * @param String Url
     */
    public function getZaakpayUpdateApiUrl() 
    {
        return 'https://api.zaakpay.com/updatetransaction';
    }

    /**
     * A wrapper method to access the checkout and order details
     * stored in the session
     * @param ? Session
     */
    public function getCheckout() 
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Instantiate state and set it to state object
     * @param string $paymentAction
     * @param Varien_Object
     */
    public function initialize($paymentAction, $stateObject)
    {
        $state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
        $stateObject->setState($state);
        $stateObject->setStatus('pending_payment');
        $stateObject->setIsNotified(false);
    }

    /**
     * Method to get the form fields with the relevant fields filled in
     * @return Array of form fields in the name=>value form 
     */
    public function getCheckoutFormFields() 
    {
        $orderIncrementId = $this->getCheckout()->getLastRealOrderId();

        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
        $this->getCheckout()->setZaakpayOrderId($orderIncrementId);
        $api = Mage::getModel('zaakpay/api_transact')->setConfigObject($this->getConfig());
        $api->setOrderId($orderIncrementId)
            ->setCurrencyCode($order->getBaseCurrencyCode())
            ->setOrder($order)
            ->setZaakpayConfig(Mage::getStoreConfig('payment/zaakpay'))
            ->setReturnUrl(Mage::getUrl('zaakpay/transact/response'));
        // export address
        $isOrderVirtual = $order->getIsVirtual();
        $api->setBillingAddress($order->getBillingAddress());
        if ($isOrderVirtual) {
            $api->setNoShipping(true);
        } elseif ($order->getShippingAddress()->validate()) {
            $api->setShippingAddress($order->getShippingAddress());
        }
        // add cart totals and line items
        $result = $api->getRequestFields();
        $this->getCheckout()->setZaakpayChecksum($api->getZaakpayChecksum());
        return $result;
    }  

    public function zaakpaySuccessOrderState() 
    {
        $config = Mage::getStoreConfig('payment/zaakpay');
        $order_status = $config['order_status'];
        switch ($order_status) {
        case "processing":
            $state = Mage_Sales_Model_Order::STATE_PROCESSING;
            break;
        case "complete":
            $state = Mage_Sales_Model_Order::STATE_COMPLETE;
            break;
        case "closed":
            $state = Mage_Sales_Model_Order::STATE_CLOSED;
            break;
        case "canceled":
            $state = Mage_Sales_Model_Order::STATE_CANCELED;
            break;
        case "holded":
            $state = Mage_Sales_Model_Order::STATE_HOLDED;
            break;
        case "pending":
        default:
            $state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
        }
        return $state;
    }

    public function capture(Varien_Object $payment, $amount) 
    {
        $order = $payment->getOrder();
        $api = Mage::getModel('zaakpay/api_update');
        $api->send($order->getIncrementId(), Zaakpay_Model_Api_Update::$STATUS_SETTLED, 'payment captured');        
        if ($api->getResponseCode() == 196) {
            Mage::throwException('Online Capture failed. Zaakpay Update Api responded Response Code: '.$api->getResponseCode() . ' Message: ' . $api->getResponseDescription());
            Mage::log('Zaakpay update api failure: orderId: '.$order->getIncrementId() . ', responseCode: ' . $api->getResponseCode(). ', responseDescription: ' . $api->getResponseDescription());
        }
    }

    public function cancel(Varien_Object $payment) 
    {
        $order = $payment->getOrder();
        $api = Mage::getModel('zaakpay/api_update');
        $api->send($order->getIncrementId(), Zaakpay_Model_Api_Update::$STATUS_CANCELLED, 'order cancelled');        
        if (!in_array($api->getResponseCode(), Zaakpay_Model_Api_Update::$CANCELLED_RESP_CODES)) {
            Mage::throwException('Request could not be completed since Zaakpay Update Api responded with update failure. Response Code: '.$api->getResponseCode());
            Mage::log('Zaakpay update api failure: orderId: '.$order->getIncrementId() . ', responseCode: ' . $api->getResponseCode(). ', responseDescription: ' . $api->getResponseDescription());
        }
    }

    public function void(Varien_Object $payment) {
        $order = $payment->getOrder();
        $api = Mage::getModel('zaakpay/api_update');
        $api->send($order->getIncrementId(), Zaakpay_Model_Api_Update::$STATUS_CANCELLED, 'order void');        
        if (!in_array($api->getResponseCode(), Zaakpay_Model_Api_Update::$CANCELLED_RESP_CODES)) {
            Mage::throwException('Request could not be completed since Zaakpay Update Api responded with update failure. Response Code: '.$api->getResponseCode());
            Mage::log('Zaakpay update api failure: orderId: '.$order->getIncrementId() . ', responseCode: ' . $api->getResponseCode(). ', responseDescription: ' . $api->getResponseDescription());
        }
    }

    // refund cannot be consumed as there is no api to capture funds
    public function refund(Varien_Object $payment, $amount) 
    {
        Mage::log('refund is getting called');
    }

    // call the check transaction api to show the current status
    public function checkStatus(Varien_Object $payment) 
    {
        $order = $payment->getOrder();
        $api = Mage::getModel('zaakpay/api_check');
        $api->check($order->getIncrementId());
        $status = $api->getResponseDescription();
        return $status;
    }
}
