<?php 

class Zaakpay_Model_Api_Check extends Varien_Object 
{

    public static $CHECK_ENDPOINT = 'https://api.zaakpay.com/checktransaction';
    
    public function check($orderId)
    {
        $request = Mage::getModel('zaakpay/api_request');
        $request->setZaakpayConfig(Mage::getStoreConfig('payment/zaakpay'))
            ->setUrl(self::$CHECK_ENDPOINT)
            ->addParam('orderId', $orderId)
            ->send();
        $this->setResponseCode($request->getResponseCode());
        $this->setResponseDescription($request->getResponseDescription());
    }
}
