<?php

/**
 * Magento
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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * One page checkout processing model
 */
require_once 'Mage/Checkout/Model/Type/Onepage.php';

class Uni_Opcheckout_Model_Type_Onepage extends Mage_Checkout_Model_Type_Onepage {

    /**
     * Enter description here...
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout() {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Enter description here...
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote() {
        return $this->getCheckout()->getQuote();
    }

    /**
     * Enter description here...
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    public function initCheckout() {
        $checkout = $this->getCheckout();
        if (is_array($checkout->getStepData())) {
            foreach ($checkout->getStepData() as $step => $data) {
                if (!($step === 'login'
                        || Mage::getSingleton('customer/session')->isLoggedIn() && $step === 'billing')) {
                    $checkout->setStepData($step, 'allow', false);
                }
            }
        }
        /*
         * want to laod the correct customer information by assiging to address
         * instead of just loading from sales/quote_address
         */
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if ($customer) {
            $this->getQuote()->assignCustomer($customer);
        }
        if ($this->getQuote()->getIsMultiShipping()) {
            $this->getQuote()->setIsMultiShipping(false);
            $this->getQuote()->save();
        }
        return $this;
    }

    /**
     * Enter description here...
     *
     * @param string $method
     * @return array
     */
    public function saveCheckoutMethod($method) {
        if (empty($method)) {
            $res = array(
                'error' => -1,
                'message' => Mage::helper('checkout')->__('Invalid data')
            );
            return $res;
        }

        $this->getQuote()->setCheckoutMethod($method)->save();
        //$this->getCheckout()->setStepData('billing', 'allow', true);
        return array();
    }

    /**
     * This method is called by One Page Checkout JS (AJAX) while saving the billing information.
     *
     * @param unknown_type $data
     * @param unknown_type $customerAddressId
     * @return unknown
     */
    public function saveBilling($data, $customerAddressId) {
        if (empty($data)) {
            $res = array(
                'error' => -1,
                'message' => Mage::helper('checkout')->__('Invalid data')
            );
            return $res;
        }

        $address = $this->getQuote()->getBillingAddress();
        if (!empty($customerAddressId)) {
            $customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
            if ($customerAddress->getId()) {
                if ($customerAddress->getCustomerId() != $this->getQuote()->getCustomerId()) {
                    return array('error' => 1,
                        'message' => Mage::helper('checkout')->__('Customer Address is not valid.')
                    );
                }
                $address->importCustomerAddress($customerAddress);
            }
        } else {
            unset($data['address_id']);
            $address->addData($data);
            //$address->setId(null);
        }

        if (($validateRes = $address->validate()) !== true) {
            $res = array(
                'error' => 1,
                'message' => $validateRes
            );
            return $res;
        }

        if (!$this->getQuote()->getCustomerId() && Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER == $this->getQuote()->getCheckoutMethod()) {
            if ($this->_customerEmailExists($address->getEmail(), Mage::app()->getWebsite()->getId())) {
                return array('error' => 1,
                    'message' => Mage::helper('checkout')->__('There is already a customer registered using this email address')
                );
            }
        }

        $address->implodeStreetAddress();

        if (!$this->getQuote()->isVirtual()) {
            /**
             * Billing address using otions
             */
            $usingCase = isset($data['use_for_shipping']) ? (int) $data['use_for_shipping'] : 0;

            switch ($usingCase) {
                case 0:
                    $shipping = $this->getQuote()->getShippingAddress();
                    $shipping->setSameAsBilling(0);
                    break;
                case 1:
                    $billing = clone $address;
                    $billing->unsAddressId()->unsAddressType();
                    $shipping = $this->getQuote()->getShippingAddress();
                    $shippingMethod = $shipping->getShippingMethod();
                    $shipping->addData($billing->getData())
                            ->setSameAsBilling(1)
                            ->setShippingMethod($shippingMethod)
                            ->setCollectShippingRates(true);
                    $this->getCheckout()->setStepData('shipping', 'complete', true);
                    break;
            }
        }

        if (true !== $result = $this->_processValidateCustomer($address)) {
            return $result;
        }

        $this->getQuote()->collectTotals();
        $this->getQuote()->save();

        $this->getCheckout()
                ->setStepData('billing', 'allow', true)
                ->setStepData('billing', 'complete', true)
                ->setStepData('shipping', 'allow', true);

        return array();
    }

    public function saveBillingStep($data, $customerAddressId) {
        if (empty($data)) {
            $res = array(
                'error' => -1,
                'message' => Mage::helper('checkout')->__('Invalid data')
            );
            return $res;
        }

        $address = $this->getQuote()->getBillingAddress();
        if (!empty($customerAddressId)) {
            $customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
            if ($customerAddress->getId()) {
                if ($customerAddress->getCustomerId() != $this->getQuote()->getCustomerId()) {
                    return array('error' => 1,
                        'message' => Mage::helper('checkout')->__('Customer Address is not valid.')
                    );
                }
                $address->importCustomerAddress($customerAddress);
            }
        } else {
            unset($data['address_id']);
            $address->addData($data);
            //$address->setId(null);
        }

        if (!$this->getQuote()->getCustomerId() && Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER == $this->getQuote()->getCheckoutMethod()) {
            if ($this->_customerEmailExists($address->getEmail(), Mage::app()->getWebsite()->getId())) {
                return array('error' => 1,
                    'message' => Mage::helper('checkout')->__('There is already a customer registered using this email address')
                );
            }
        }

        $address->implodeStreetAddress();

        if (!$this->getQuote()->isVirtual()) {
            /**
             * Billing address using otions
             */
            $usingCase = isset($data['use_for_shipping']) ? (int) $data['use_for_shipping'] : 0;

            switch ($usingCase) {
                case 0:
                    $shipping = $this->getQuote()->getShippingAddress();
                    $shipping->setSameAsBilling(0);
                    break;
                case 1:
                    $billing = clone $address;
                    $billing->unsAddressId()->unsAddressType();
                    $shipping = $this->getQuote()->getShippingAddress();
                    $shippingMethod = $shipping->getShippingMethod();
                    $shipping->addData($billing->getData())
                            ->setSameAsBilling(1)
                            ->setShippingMethod($shippingMethod)
                            ->setCollectShippingRates(true);
                    $this->getCheckout()->setStepData('shipping', 'complete', true);
                    break;
            }
        }

        if (true !== $result = $this->_processValidateCustomer($address)) {
            return $result;
        }

        $this->getQuote()->collectTotals();
        $this->getQuote()->save();

        $this->getCheckout()
                ->setStepData('billing', 'allow', true)
                ->setStepData('billing', 'complete', true)
                ->setStepData('shipping', 'allow', true);

        return array();
    }

    /**
     * Validate customer data and set some its data for further usage in quote
     * Will return either true or array with error messages
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return true|array
     */
    protected function _processValidateCustomer(Mage_Sales_Model_Quote_Address $address) {
        // set customer date of birth for further usage
        $dob = '';
        if ($address->getDob()) {
            $dob = Mage::app()->getLocale()->date($address->getDob(), null, null, false)->toString('yyyy-MM-dd');
            $this->getQuote()->setCustomerDob($dob);
        }

        // set customer tax/vat number for further usage
        if ($address->getTaxvat()) {
            $this->getQuote()->setCustomerTaxvat($address->getTaxvat());
        }

        // invoke customer model, if it is registering
        if (Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER == $this->getQuote()->getCheckoutMethod()) {
            // set customer password hash for further usage
            $customer = Mage::getModel('customer/customer');
            $this->getQuote()->setPasswordHash($customer->encryptPassword($address->getCustomerPassword()));

            // validate customer
            foreach (array(
        'firstname' => 'firstname',
        'lastname' => 'lastname',
        'email' => 'email',
        'password' => 'customer_password',
        'confirmation' => 'confirm_password',
        'taxvat' => 'taxvat',
            ) as $key => $dataKey) {
                $customer->setData($key, $address->getData($dataKey));
            }
            if ($dob) {
                $customer->setDob($dob);
            }
            $validationResult = $customer->validate();
            if (true !== $validationResult && is_array($validationResult)) {
                return array(
                    'error' => -1,
                    'message' => implode(', ', $validationResult)
                );
            }
        } elseif (Mage_Sales_Model_Quote::CHECKOUT_METHOD_GUEST == $this->getQuote()->getCheckoutMethod()) {
            $email = $address->getData('email');
            if (!Zend_Validate::is($email, 'EmailAddress')) {
                return array(
                    'error' => -1,
                    'message' => Mage::helper('checkout')->__('Invalid email address "%s"', $email)
                );
            }
        }

        return true;
    }

    public function saveShipping($data, $customerAddressId) {
        if (empty($data)) {
            $res = array(
                'error' => -1,
                'message' => Mage::helper('checkout')->__('Invalid data')
            );
            return $res;
        }

        $address = $this->getQuote()->getShippingAddress();

        if (!empty($customerAddressId)) {
            $customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
            if ($customerAddress->getId()) {
                if ($customerAddress->getCustomerId() != $this->getQuote()->getCustomerId()) {
                    return array('error' => 1,
                        'message' => Mage::helper('checkout')->__('Customer Address is not valid.')
                    );
                }
                $address->importCustomerAddress($customerAddress);
            }
        } else {
            unset($data['address_id']);
            $address->addData($data);
        }
        $address->implodeStreetAddress();
        $address->setCollectShippingRates(true);

        if (($validateRes = $address->validate()) !== true) {
            $res = array(
                'error' => 1,
                'message' => $validateRes
            );
            return $res;
        }

        $this->getQuote()->collectTotals()->save();

        $this->getCheckout()
                ->setStepData('shipping', 'complete', true)
                ->setStepData('shipping_method', 'allow', true);

        return array();
    }

    public function saveShippingMethod($shippingMethod) {
        if (empty($shippingMethod)) {
            $res = array(
                'error' => -1,
                'message' => Mage::helper('checkout')->__('Invalid shipping method.')
            );
            return $res;
        }
        $rate = $this->getQuote()->getShippingAddress()->getShippingRateByCode($shippingMethod);
        if (!$rate) {
            $res = array(
                'error' => -1,
                'message' => Mage::helper('checkout')->__('Invalid shipping method.')
            );
            return $res;
        }
        $this->getQuote()->getShippingAddress()->setShippingMethod($shippingMethod);
        $this->getQuote()->collectTotals()->save();

        $this->getCheckout()
                ->setStepData('shipping_method', 'complete', true)
                ->setStepData('payment', 'allow', true);

        return array();
    }

    public function savePayment($data) {
        if (empty($data)) {
            return array('error' => -1, 'message' => Mage::helper('checkout')->__('Invalid data'));
        }

        $quote = $this->getQuote();
        if ($quote->isVirtual()) {
            $quote->getBillingAddress()->setPaymentMethod(isset($data['method']) ? $data['method'] : null);
        } else {
            $quote->getShippingAddress()->setPaymentMethod(isset($data['method']) ? $data['method'] : null);
        }

        // shipping totals may be affected by payment method
        if (!$quote->isVirtual() && $quote->getShippingAddress()) {
            $quote->getShippingAddress()->setCollectShippingRates(true);
        }
        $payment = $quote->getPayment();
        $payment->importData($data);

        $quote->save();

        $this->getCheckout()
                ->setStepData('payment', 'complete', true)
                ->setStepData('review', 'allow', true);

        return array();
    }

    protected function validateOrder() {
        $helper = Mage::helper('checkout');
        if ($this->getQuote()->getIsMultiShipping()) {
            Mage::throwException($helper->__('Invalid checkout type.'));
        }

        if (!$this->getQuote()->isVirtual()) {
            $address = $this->getQuote()->getShippingAddress();
            $addressValidation = $address->validate();
            if ($addressValidation !== true) {
                Mage::throwException($helper->__('Please check shipping address information.'));
            }
            $method = $address->getShippingMethod();
            $rate = $address->getShippingRateByCode($method);
            if (!$this->getQuote()->isVirtual() && (!$method || !$rate)) {
                Mage::throwException($helper->__('Please specify shipping method.'));
            }
        }

        $addressValidation = $this->getQuote()->getBillingAddress()->validate();
        if ($addressValidation !== true) {
            Mage::throwException($helper->__('Please check billing address information.'));
        }

        if (!($this->getQuote()->getPayment()->getMethod())) {
            Mage::throwException($helper->__('Please select valid payment method.'));
        }
    }

    /**
     * Enter description here...
     *
     * @return array
     */
    public function saveOrder() {
        $this->validateOrder();
        $billing = $this->getQuote()->getBillingAddress();
        if (!$this->getQuote()->isVirtual()) {
            $shipping = $this->getQuote()->getShippingAddress();
        }
        switch ($this->getQuote()->getCheckoutMethod()) {
            case Mage_Sales_Model_Quote::CHECKOUT_METHOD_GUEST:
                if (!$this->getQuote()->isAllowedGuestCheckout()) {
                    Mage::throwException(Mage::helper('checkout')->__('Sorry, guest checkout is not enabled. Please try again or contact store owner.'));
                }
                $this->getQuote()->setCustomerId(null)
                        ->setCustomerEmail($billing->getEmail())
                        ->setCustomerIsGuest(true)
                        ->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
                break;

            case Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER:
                $customer = Mage::getModel('customer/customer');
                /* @var $customer Mage_Customer_Model_Customer */

                $customerBilling = $billing->exportCustomerAddress();
                $customer->addAddress($customerBilling);

                if (!$this->getQuote()->isVirtual() && !$shipping->getSameAsBilling()) {
                    $customerShipping = $shipping->exportCustomerAddress();
                    $customer->addAddress($customerShipping);
                }

                if ($this->getQuote()->getCustomerDob() && !$billing->getCustomerDob()) {
                    $billing->setCustomerDob($this->getQuote()->getCustomerDob());
                }

                if ($this->getQuote()->getCustomerTaxvat() && !$billing->getCustomerTaxvat()) {
                    $billing->setCustomerTaxvat($this->getQuote()->getCustomerTaxvat());
                }

                Mage::helper('core')->copyFieldset('checkout_onepage_billing', 'to_customer', $billing, $customer);

                $customer->setPassword($customer->decryptPassword($this->getQuote()->getPasswordHash()));
                $customer->setPasswordHash($customer->hashPassword($customer->getPassword()));

                $this->getQuote()->setCustomer($customer);
                break;

            default:
                $customer = Mage::getSingleton('customer/session')->getCustomer();

                if (!$billing->getCustomerId() || $billing->getSaveInAddressBook()) {
                    $customerBilling = $billing->exportCustomerAddress();
                    $customer->addAddress($customerBilling);
                }
                if (!$this->getQuote()->isVirtual() &&
                        ((!$shipping->getCustomerId() && !$shipping->getSameAsBilling()) ||
                        (!$shipping->getSameAsBilling() && $shipping->getSaveInAddressBook()))) {

                    $customerShipping = $shipping->exportCustomerAddress();
                    $customer->addAddress($customerShipping);
                }
                $customer->setSavedFromQuote(true);
                $customer->save();

                $changed = false;
                if (isset($customerBilling) && !$customer->getDefaultBilling()) {
                    $customer->setDefaultBilling($customerBilling->getId());
                    $changed = true;
                }
                if (!$this->getQuote()->isVirtual() && isset($customerBilling) && !$customer->getDefaultShipping() && $shipping->getSameAsBilling()) {
                    $customer->setDefaultShipping($customerBilling->getId());
                    $changed = true;
                } elseif (!$this->getQuote()->isVirtual() && isset($customerShipping) && !$customer->getDefaultShipping()) {
                    $customer->setDefaultShipping($customerShipping->getId());
                    $changed = true;
                }

                if ($changed) {
                    $customer->save();
                }
        }

        $this->getQuote()->reserveOrderId();
        $convertQuote = Mage::getModel('sales/convert_quote');
        /* @var $convertQuote Mage_Sales_Model_Convert_Quote */
        //$order = Mage::getModel('sales/order');
        if ($this->getQuote()->isVirtual()) {
            $order = $convertQuote->addressToOrder($billing);
        } else {
            $order = $convertQuote->addressToOrder($shipping);
        }
        /* @var $order Mage_Sales_Model_Order */
        $order->setBillingAddress($convertQuote->addressToOrderAddress($billing));

        if (!$this->getQuote()->isVirtual()) {
            $order->setShippingAddress($convertQuote->addressToOrderAddress($shipping));
        }

        $order->setPayment($convertQuote->paymentToOrderPayment($this->getQuote()->getPayment()));

        foreach ($this->getQuote()->getAllItems() as $item) {
            $orderItem = $convertQuote->itemToOrderItem($item);
            if ($item->getParentItem()) {
                $orderItem->setParentItem($order->getItemByQuoteItemId($item->getParentItem()->getId()));
            }
            $order->addItem($orderItem);
        }

        /**
         * We can use configuration data for declare new order status
         */
        Mage::dispatchEvent('checkout_type_onepage_save_order', array('order' => $order, 'quote' => $this->getQuote()));
        // check again, if customer exists
        if ($this->getQuote()->getCheckoutMethod() == Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER) {
            if ($this->_customerEmailExists($customer->getEmail(), Mage::app()->getWebsite()->getId())) {
                Mage::throwException(Mage::helper('checkout')->__('There is already a customer registered using this email address'));
            }
        }
        $order->place();

        if ($this->getQuote()->getCheckoutMethod() == Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER) {
            $customer->save();
            $customerBillingId = $customerBilling->getId();
            if (!$this->getQuote()->isVirtual()) {
                $customerShippingId = isset($customerShipping) ? $customerShipping->getId() : $customerBillingId;
                $customer->setDefaultShipping($customerShippingId);
            }
            $customer->setDefaultBilling($customerBillingId);
            $customer->save();

            $this->getQuote()->setCustomerId($customer->getId());

            $order->setCustomerId($customer->getId());
            Mage::helper('core')->copyFieldset('customer_account', 'to_order', $customer, $order);

            $billing->setCustomerId($customer->getId())->setCustomerAddressId($customerBillingId);
            if (!$this->getQuote()->isVirtual()) {
                $shipping->setCustomerId($customer->getId())->setCustomerAddressId($customerShippingId);
            }

            if ($customer->isConfirmationRequired()) {
                $customer->sendNewAccountEmail('confirmation');
            } else {
                $customer->sendNewAccountEmail();
            }
        }

        /**
         * a flag to set that there will be redirect to third party after confirmation
         * eg: paypal standard ipn
         */
        $version = Mage::getVersion();
        $redirectUrl = $this->getQuote()->getPayment()->getOrderPlaceRedirectUrl();
        $order->save();
        if (!$redirectUrl) {
            if ($version == '1.8.1.0') {
                $order->sendNewOrderEmail();
                $order->setEmailSent(true);
            } else {
                $order->setEmailSent(true);
            }
            
            $order_id = $order->getId(); //use your own order id
            $order = Mage::getModel("sales/order")->load($order_id); //load order by order id
            $ordered_items = $order->getAllItems();
            Foreach ($ordered_items as $item) {
                $productId = $item->getItemId();
            }
            $_product = Mage::getModel('catalog/product')->load($productId);
            $customer = Mage::getModel('customer/customer')->load($_product->getUserid());
            $productOption = $item->getProductOptions();
            $to = str_replace("@", ".", $productOption['info_buyRequest']['todate']);
            $from = str_replace("@", ".", $productOption['info_buyRequest']['fromdate']);
            $todate = date('Y-m-d', strtotime(str_replace('.', '/', $to)));
            $fromdate = date('Y-m-d', strtotime(str_replace('.', '/', $from)));
            $date1 = new DateTime($fromdate);
            $date2 = new Datetime($todate);
            $interval = $date1->diff($date2);
            $diff = $interval->format('%R%d Night');

            $to = $_product->getHostemail();
            $from = 'sales@restostay.com';
            $subject = 'Restostay Reservation confirmation Number ' . $order->getIncrementId() . ' – ' . $_product->getName();
            $message = '<html>
<head>
  <title>Restostay Reservation confirmation</title>
</head>
<body><p><a href="http://www.restostay.com/index.php/" target="_blank"><img src="http://www.restostay.com/skin/frontend/default/stylish/images/logo.png"></a></p>
								<br clear="ALL">
								<p>Dear  ' . $customer->getFirstname() . ' ' . $customer->getLastname() . '<br>
								  The  following Reservation was made on Restostay for your ' . $_product->getName() . ' <br>
								  If  you have any questions about this order please contact us at  support@restostay.com or call us at +1 91-8130-596-780 (India) Monday - Friday,  8am - 5pm.<br>
								  You  can find Reservation details here. Thank you again for your continued patronage.<br>
								  Reservation  #' . $order->getIncrementId() . ' <br>
								  Reservation  details:<br>
								  Guest  House Name : ' . $_product->getName() . '<br>
								  Guest  House Address  : <br>
								  ' . $_product->getCity() . ',  ' . $_product->getState() . ', ' . $_product->getCountry() . '<br>
								  Phone no:' . $_product->getPhoneno() . ' <br>
								  Reservation Status:' . $order->getStatus() . '<br>
								  Check In : ' . $fromdate . ' <br>
								  Check Out : ' . $todate . '<br>
								  Number of nights : ' . $diff . ' <br>
								  Number of Rooms : ' . $productOption['info_buyRequest']['rooms'] . ' <br>
								  Number of Guests : ' . $productOption['info_buyRequest']['accomodate'] . ' Adults, ' . $productOption['info_buyRequest']['child'] . ' Children <br>
								  Total Amount:' . round($order->getGrandTotal(), 2) . ' amount (Including Processing fees &amp; Taxes)</p></body>
</html>';

            // To send HTML mail, the Content-type header must be set
            $headers = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

            // Additional headers
            $headers .= 'To: ' . $customer->getFirstname() . ' <' . $to . '>' . "\r\n";
            $headers .= 'From: Restostay Reservation confirmation <support@restostay.com>' . "\r\n";

            // Mail it
            mail($to, $subject, $message, $headers);
        }

        Mage::dispatchEvent('checkout_type_onepage_save_order_after', array('order' => $order, 'quote' => $this->getQuote()));

        /**
         * need to have somelogic to set order as new status to make sure order is not finished yet
         * quote will be still active when we send the customer to paypal
         */
        $this->getCheckout()->setLastQuoteId($this->getQuote()->getId());
        $this->getCheckout()->setLastOrderId($order->getId());
        $this->getCheckout()->setLastRealOrderId($order->getIncrementId());
        $this->getCheckout()->setRedirectUrl($redirectUrl);

        /**
         * we only want to send to customer about new order when there is no redirect to third party
         */
//        if (!$redirectUrl) {
//            try {
//                $order->sendNewOrderEmail();
//            } catch (Exception $e) {
//                Mage::logException($e);
//            }
//        }
        if ($this->getQuote()->getCheckoutMethod(true) == Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER
                && !Mage::getSingleton('customer/session')->isLoggedIn()) {
            /**
             * we need to save quote here to have it saved with Customer Id.
             * so when loginById() executes checkout/session method loadCustomerQuote
             * it would not create new quotes and merge it with old one.
             */
            $this->getQuote()->save();
            if ($customer->isConfirmationRequired()) {
                Mage::getSingleton('checkout/session')->addSuccess(Mage::helper('customer')->__('Account confirmation is required. Please, check your e-mail for confirmation link. To resend confirmation email please <a href="%s">click here</a>.', Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail())
                        ));
            } else {
                Mage::getSingleton('customer/session')->loginById($customer->getId());
            }
        }

        //Setting this one more time like control flag that we haves saved order
        //Must be checkout on success page to show it or not.
        $this->getCheckout()->setLastSuccessQuoteId($this->getQuote()->getId());

        $this->getQuote()->setIsActive(false);
        $this->getQuote()->save();

        return $this;
    }

    /**
     * Enter description here...
     *
     * @return string
     */
    public function getLastOrderId() {
        /*
          $customerSession = Mage::getSingleton('customer/session');
          if (!$customerSession->isLoggedIn()) {
          $this->_redirect('checkout/cart');
          return;
          }
          $collection = Mage::getResourceModel('sales/order_collection')
          ->addAttributeSelect('self/real_order_id')
          ->addAttributeFilter('self/customer_id', $customerSession->getCustomerId())
          ->setOrder('self/created_at', 'DESC')
          ->setPageSize(1)
          ->loadData();
          foreach ($collection as $order) {
          $orderId = $order->getRealOrderId();
          }
         */
        $order = Mage::getModel('sales/order');
        $order->load($this->getCheckout()->getLastOrderId());
        $orderId = $order->getIncrementId();
        return $orderId;
    }

    public function saveShippingStep($sdata, $customerAddressId) {
        if (empty($sdata)) {
            $res = array(
                'error' => -1,
                'message' => Mage::helper('checkout')->__('Invalid data')
            );
            return $res;
        }
        $address = $this->getQuote()->getShippingAddress();
        if (!empty($customerAddressId)) {
            $customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
            if ($customerAddress->getId()) {
                if ($customerAddress->getCustomerId() != $this->getQuote()->getCustomerId()) {
                    return array('error' => 1,
                        'message' => Mage::helper('checkout')->__('Customer Address is not valid.')
                    );
                }
                $address->importCustomerAddress($customerAddress);
            }
        } else {
            unset($sdata['address_id']);
            $address->addData($sdata);
        }
        $address->implodeStreetAddress();
        $address->setCollectShippingRates(true);
        $this->getQuote()->collectTotals()->save();

        $this->getCheckout()
                ->setStepData('shipping', 'complete', true)
                ->setStepData('shipping_method', 'allow', true);

        return array();
    }

}
