<?php

require_once Mage::getBaseDir('lib') . '/Zaakpay/checksum.php';

/**
 * Zaakpay Transact API
 */
class Zaakpay_Model_Api_Transact extends Varien_Object
{

    protected $_checksum = null;

    protected $_globalMap = array(
        // commands
        'merchantIdentifier' => '',
        'orderId' => '',
        'returnUrl' => '',
        'buyerEmail' => '',
        'buyerFirstName' => '',
        'buyerLastName' => '',
        'buyerAddress' => '',
        'buyerCity' => '',
        'buyerState' => '',
        'buyerCountry' => '',
        'buyerPincode' => '',
        'buyerPhoneNumber' => '',
        'txnType' => '1',
        'zpPayOption' => '1',
        'mode' => '1',
        'currency' => 'USD',
        'amount' => '0',
        'merchantIpAddress' => '',
        'purpose' => '',
        'productDescription' => '',
        'product1Description' => '',
        'product2Description' => '',
        'product3Description' => '',
        'product4Description' => '',
        'shipToAddress' => '',
        'shipToCity' => '',
        'shipToState' => '',
        'shipToCountry' => '',
        'shipToPincode' => '',
        'shipToPhoneNumber' => '',
        'shipToFirstname' => '',
        'shipToLastname' => '',
        'txnDate' => '',
    );

    protected $_mandatory = array(
        'merchantIdentifier',
        'orderId',
        'buyerEmail',
        'buyerFirstName',
        'buyerLastName',
        'buyerAddress',
        'buyerCity',
        'buyerState',
        'buyerCountry',
        'buyerPincode',
        'buyerPhoneNumber',
        'txnType',
        'zpPayOption',
        'mode',
        'currency',
        'amount',
        'purpose',
        'merchantIpAddress',
        'productDescription',
        'txnDate'
    );

    private function _validateFields($fields) 
    {
        // mode is an exceptional case here as it may take a value '0' or 0 here so we handle that first
        if (!in_array($fields['mode'], array(0, 1))) {
            throw new Exception('Zaakpay requires that the field mode can take only values 0 and 1');
        }
        unset($fields['mode']);
        foreach ($fields as $key=>$value) {
            if (in_array($key, $this->_mandatory) && !$value) {
                throw new Exception('Zaakpay requires the field ' . $key . ' to be mandatory.');
            }
        }        
    }

    private function _buildRequestFields() 
    {
        $fields = $this->_globalMap;
        $zaakpayConfig = $this->getZaakpayConfig();
        $order = $this->getOrder();
        $order_data = $order->getData();
        $billingAddress = $this->getBillingAddress();
        $shippingAddress = $this->getShippingAddress();
        $amount = $this->_convertAmount($order->getGrandTotal(), $order->getOrderCurrencyCode());
        $currency = 'INR';
        // merchant identifier
        $fields = array_merge($this->_globalMap, array(
            'merchantIdentifier' => $zaakpayConfig['merchant_id'],
            'orderId' => $order->getIncrementId(),
            #'returnUrl' => $this->getReturnUrl(),
            'buyerEmail' => $order->getCustomerEmail(),
            'buyerFirstName' => $order->getCustomerLastname(),
            'buyerLastName' => $order->getCustomerFirstname(),
            'buyerAddress' => $this->_joinAddressStreet($billingAddress->getStreet()),
            'buyerCity' => $billingAddress->getCity(),
            'buyerState' => $billingAddress->getRegion(),
            'buyerCountry' => $this->_getCountryNameByCode($billingAddress->getCountry()),
            'buyerPincode' => $billingAddress->getPostcode(),
            'buyerPhoneNumber' => $billingAddress->getTelephone(),
            'mode' => $zaakpayConfig['sandbox_mode'] ? 0 : 1,
            'currency' => $currency,
            'amount' => $amount,
            'merchantIpAddress' => $order->getRemoteIp(),
            'purpose' => '1',
            'productDescription' => 'Order Id #' . $order->getIncrementId(),
            'product1Description' => '',
            'product2Description' => '',
            'product3Description' => '',
            'product4Description' => '',
            'txnDate' => date('Y-m-d', strtotime($order->getCreatedAt())),
        ));
        // set the shipping address too if the order is not virtual
        if (!$order->getIsVirtual()) {
            $fields = array_merge($fields, array(
                'shipToAddress' => $this->_joinAddressStreet($shippingAddress->getStreet()),
                'shipToCity' => $shippingAddress->getCity(),
                'shipToState' => $shippingAddress->getRegion(),
                'shipToCountry' => $this->_getCountryNameByCode($shippingAddress->getCountry()),
                'shipToPincode' => $shippingAddress->getPostcode(),
                'shipToPhoneNumber' => $shippingAddress->getTelephone(),
                'shipToFirstname' => $order->getCustomerFirstname(),
                'shipToLastname' => $order->getCustomerLastname(),
            ));
        }
        return $fields;
    }

    /**
     * Method to return the country name by the country code
     */
    private function _getCountryNameByCode($code) 
    {
        $countryModel = Mage::getModel('directory/country')->loadByCode($code);
        return ucfirst($countryModel->getName());        
    }

    /**
     * Method to convert the amount to INR
     * @param decimal $amount
     * @param String $currency
     */
    private function _convertAmount($amount, $currencyCode) 
    {   
	    $backamount = 0 ;
        if ($currencyCode == 'INR' ) {
            $backamount = floor($amount * 100);           
        }
		if (($currencyCode != 'INR') && ($currencyCode != 'USD')) {
            $amount = Mage::helper('directory')->currencyConvert($amount, $currencyCode, 'INR');
			$backamount = floor($amount * 100);            
        }
		if ($currencyCode == 'USD') {
            $backamount = floor($amount * 100);
        }
		return $backamount;
        
    }

    /**
     * Method to join Street 1 and Street 2 in an address to a single string
     * @param mixed $address (String|Array)
     * @return String $address
     */
    private function _joinAddressStreet($address) 
    {
        if (is_array($address)) {            
            $address = array_map('trim', $address);
            $joined = implode(" ", $address);
            return substr($joined, 0, 30);
        }
        return $address;
    }

    /**
     * Method to concatenate the fields into a string
     * using which the checksum will be creaeted
     * @param Array $fields
     * @param String
     */
    public function _concatFields($fields) 
    {
        unset($fields['checksum']);
        #ksort($fields);
        return "'" . implode("'", $fields) . "'";
    }

    public function getRequestFields() 
    {
        $fields = $this->_buildRequestFields();
        // pass it through validate so that an exception is thrown
        $this->_validateFields($fields);
        $all = Checksum::getAllParams($fields);
	error_log("Logging stripped params : " . $all);
        $zaakpayConfig = $this->getZaakpayConfig();
        $checksum = Checksum::calculateChecksum($zaakpayConfig['secret_key'], $all);
		error_log("Logging stripped params : " . $all);
	error_log('Logging key used to produce checksum : ' . $zaakpayConfig['secret_key']);
	error_log('Logging checksum : ' . $checksum);
        $this->_checksum = $checksum;
        // var_dump($all, $checksum);
        #ksort($fields);
        // first sort by key and then append checksum in the end 
        $fields['checksum'] = $checksum;
        return $fields;
    }

    public function getZaakpayChecksum() 
    {
        return $this->_checksum;
    }
}

