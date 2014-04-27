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
 * @category   Mage
 * @package    Mage_Checkout
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
require_once 'Mage/Checkout/controllers/OnepageController.php';

class Uni_Opcheckout_OnepageController extends Mage_Checkout_OnepageController {

    protected $_sectionUpdateFunctions = array(
        'payment-method' => '_getPaymentMethodsHtml',
        'shipping-method' => '_getShippingMethodsHtml',
        'review' => '_getReviewHtml',
    );

    /**
     * @return Mage_Checkout_OnepageController
     */
    public function successAction() {
        $this->_redirect('checkout/onepage/success');
    }

    public function failureAction() {
        $this->_redirect('checkout/onepage/failure');
    }

    protected function _expireAjax() {
        if (!$this->getOnepage()->getQuote()->hasItems()) {
            return array('redirect' => Mage::getUrl('checkout/onepage'));
        }
        if (!$this->getOnepage()->getQuote()->hasItems()
                || $this->getOnepage()->getQuote()->getHasError()
                || $this->getOnepage()->getQuote()->getIsMultiShipping()) {
            $this->_ajaxRedirectResponse();
            exit;
        }
        $action = $this->getRequest()->getActionName();
        if (Mage::getSingleton('checkout/session')->getCartWasUpdated(true)
                && !in_array($action, array('index', 'progress', 'saveBillingStep', 'saveShippingStep', 'paymentmethod', 'saveOrderCustom', 'saveShippingMethod'))) {
            $this->_ajaxRedirectResponse();
            exit;
        }
        Mage::getSingleton('core/translate_inline')->setIsAjaxRequest(true);
    }

    protected function _getShippingMethodsHtml() {
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('opcheckout_onepage_shippingmethod');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();
        return $output;
    }

    protected function _getPaymentMethodsHtml() {
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('opcheckout_onepage_paymentmethod');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();
        return $output;
    }

    protected function _getAdditionalHtml() {
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('opcheckout_onepage_additional');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();
        return $output;
    }

    /**
     * Enter description here...
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    public function getOnepage() {
        return Mage::getSingleton('opcheckout/type_onepage');
    }

    /**
     * Checkout page
     */
    public function indexAction() {
        $this->_redirect('checkout/onepage');
    }

    /**
     * Address JSON
     */
    public function getAddressAction() {
        $this->_expireAjax();
        $addressId = $this->getRequest()->getParam('address', false);
        if ($addressId) {
            $address = $this->getOnepage()->getAddress($addressId);
            $this->getResponse()->setHeader('Content-type', 'application/x-json');
            $this->getResponse()->setBody($address->toJson());
        }
    }

    public function saveMethodAction() {
        $this->_expireAjax();
        if ($this->getRequest()->isPost()) {
            $method = $this->getRequest()->getPost('method');
            $result = $this->getOnepage()->saveCheckoutMethod($method);
            $this->getResponse()->setBody(Zend_Json::encode($result));
        }
    }

    /**
     * save checkout billing address
     */
    public function saveBillingAction() {
        $this->_expireAjax();
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('billing', array());
            $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);
            $result = $this->getOnepage()->saveBilling($data, $customerAddressId);

            if (!isset($result['error'])) {
                /* check quote for virtual */
                if ($this->getOnepage()->getQuote()->isVirtual()) {
                    $result['goto_section'] = 'payment';
                    $result['update_section'] = array(
                        'name' => 'payment-method',
                        'html' => $this->_getPaymentMethodsHtml()
                    );
                } elseif (isset($data['use_for_shipping']) && $data['use_for_shipping'] == 1) {

                    $result['goto_section'] = 'shipping_method';

                    $result['update_section'] = array(
                        'name' => 'shipping-method',
                        'html' => $this->_getShippingMethodsHtml()
                    );

                    $result['allow_sections'] = array('shipping');
                    $result['duplicateBillingInfo'] = 'true';
                } else {
                    $result['goto_section'] = 'shipping';
                }
            }

            $this->getResponse()->setBody(Zend_Json::encode($result));
        }
    }

    public function saveBillingStepAction() {
        if ($resError = $this->_expireAjax()) {
            $this->getResponse()->setBody(Zend_Json::encode($resError));
            return;
        }
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('billing', array());
            $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);
            $result = $this->getOnepage()->saveBillingStep($data, $customerAddressId);

            if (!isset($result['error'])) {
                /* check quote for virtual */
                if ($this->getOnepage()->getQuote()->isVirtual()) {
                    $result['goto_section'] = 'payment';
                    $result['update_section'] = array(
                        'name' => 'payment-method',
                        'html' => $this->_getPaymentMethodsHtml()
                    );
                } elseif (isset($data['use_for_shipping']) && $data['use_for_shipping'] == 1) {

                    $result['goto_section'] = 'shipping_method';

                    $result['update_section'] = array(
                        'name' => 'shipping-method',
                        'html' => $this->_getShippingMethodsHtml()
                    );

                    $result['allow_sections'] = array('shipping');
                    $result['duplicateBillingInfo'] = 'true';
                } else {
                    $result['goto_section'] = 'shipping';
                }
            }

            $this->getResponse()->setBody(Zend_Json::encode($result));
        }
    }

    public function saveShippingAction() {
        $this->_expireAjax();
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('shipping', array());
            $customerAddressId = $this->getRequest()->getPost('shipping_address_id', false);
            $result = $this->getOnepage()->saveShipping($data, $customerAddressId);

            if (!isset($result['error'])) {
                $result['goto_section'] = 'shipping_method';
                $result['update_section'] = array(
                    'name' => 'shipping-method',
                    'html' => $this->_getShippingMethodsHtml()
                );
            }

//            $this->loadLayout('checkout_onepage_shippingMethod');
//            $result['shipping_methods_html'] = $this->getLayout()->getBlock('root')->toHtml();
//            $result['shipping_methods_html'] = $this->_getShippingMethodsHtml();

            $this->getResponse()->setBody(Zend_Json::encode($result));
        }
    }

    public function saveShippingMethodAction() {
        if ($resError = $this->_expireAjax()) {
            $this->getResponse()->setBody(Zend_Json::encode($resError));
            return;
        }
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('shipping_method', '');
            $result = $this->getOnepage()->saveShippingMethod($data);
            /*
              $result will have erro data if shipping method is empty
             */
            if (!$result) {
                Mage::dispatchEvent('opcheckout_controller_onepage_save_shipping_method', array('request' => $this->getRequest(), 'quote' => $this->getOnepage()->getQuote()));
                $this->getResponse()->setBody(Zend_Json::encode($result));

                $result['goto_section'] = 'payment';
                $result['update_section'] = array(
                    'name' => 'payment-method',
                    'html' => $this->_getPaymentMethodsHtml()
                );

//                $result['payment_methods_html'] = $this->_getPaymentMethodsHtml();
            }
            $this->getResponse()->setBody(Zend_Json::encode($result));
        }
    }

    public function paymentmethodAction() {
        if ($resError = $this->_expireAjax()) {
            $this->getResponse()->setBody(Zend_Json::encode($resError));
            return;
        }
        $result = array();
        // saving payment to quote
        try {
            $data = $this->getRequest()->getPost('payment', array());
            if (!empty($data)) {
                if ($this->getOnepage()->getQuote()->isVirtual()) {
                    $this->getOnepage()->getQuote()->getBillingAddress()->setPaymentMethod(isset($data['method']) ? $data['method'] : null);
                } else {
                    $this->getOnepage()->getQuote()->getShippingAddress()->setPaymentMethod(isset($data['method']) ? $data['method'] : null);
                }
                $payment = $this->getOnepage()->getQuote()->getPayment()->setMethod($data['method']);
                $this->getOnepage()->getQuote()->save();
            }
        } catch (Exception $e) {
            $result['error'] = true;
            Mage::log('OPcheckout :: ' . $e->getMessage());
        }
        $result['method'] = $this->getOnepage()->getQuote()->getPayment()->getMethod();
        $result['html'] = $this->_getPaymentMethodsHtml();
        $this->getResponse()->setBody(Zend_Json::encode($result));
        return;
    }

    public function savePaymentAction() {
        $this->_expireAjax();
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('payment', array());
            /*
             * first to check payment information entered is correct or not
             */

            try {
                $result = $this->getOnepage()->savePayment($data);
            } catch (Mage_Payment_Exception $e) {
                if ($e->getFields()) {
                    $result['fields'] = $e->getFields();
                }
                $result['error'] = $e->getMessage();
            } catch (Exception $e) {
                $result['error'] = $e->getMessage();
            }
            $redirectUrl = $this->getOnePage()->getQuote()->getPayment()->getCheckoutRedirectUrl();
            if (empty($result['error']) && !$redirectUrl) {
                $this->loadLayout('opcheckout_onepage_review');

                $result['goto_section'] = 'review';
                $result['update_section'] = array(
                    'name' => 'review',
                    'html' => $this->_getReviewHtml()
                );

//                $result['review_html'] = $this->getLayout()->getBlock('root')->toHtml();
            }

            if ($redirectUrl) {
                $result['redirect'] = $redirectUrl;
            }

            $this->getResponse()->setBody(Zend_Json::encode($result));
        }
    }

    public function saveShippingStepAction() {
        if ($resError = $this->_expireAjax()) {
            $this->getResponse()->setBody(Zend_Json::encode($resError));
            return;
        }
        if ($this->getRequest()->isPost()) {
            $bdata = $this->getRequest()->getPost('billing', array());
            $sdata = $this->getRequest()->getPost('shipping', array());
            $customerAddressId = $this->getRequest()->getPost('shipping_address_id', false);
            $result = $this->getOnepage()->saveShippingStep($sdata, $customerAddressId);

            if (!isset($result['error'])) {
                $result['goto_section'] = 'shipping_method';
                $result['update_section'] = array(
                    'name' => 'shipping-method',
                    'html' => $this->_getShippingMethodsHtml()
                );
            }
            $this->getResponse()->setBody(Zend_Json::encode($result));
        }
    }

    public function saveOrderCustomAction() {
        if ($this->getRequest()->getParam('submitCustomCheckout')) {
            if ($resError = $this->_expireAjax()) {
                $this->getResponse()->setBody(Zend_Json::encode($resError));
                return;
            }
            $opCheckoutOrderComment = $this->getRequest()->getParam('order_comment');
            $deliverydate = $this->getRequest()->getParam('shipping_arrival_date');
            $giftmessage = $this->getRequest()->getParam('giftmessage');
            $billingAddress = $this->getRequest()->getParam('billing');
            $shippingAddress = $this->getRequest()->getParam('shipping');
            $paymentMethod = $this->getRequest()->getParam('payment');
            $shippingMethod = $this->getRequest()->getParam('shipping_method');
            $checkout = Mage::getSingleton('checkout/type_onepage');
            $login = $this->getRequest()->getParam('create_account');
            //$login=false;
            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $res = $checkout->saveCheckoutMethod(Mage_Sales_Model_Quote::CHECKOUT_METHOD_LOGIN_IN);
                $billingCustomerAddressId = $this->getRequest()->getParam('billing_address_id');
                $shippingCustomerAddressId = $this->getRequest()->getParam('shipping_address_id');
            } else {
                if ($login) {
                    $res = $checkout->saveCheckoutMethod(Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER);
                    $billingCustomerAddressId = false; //$billingAddress['address_id'];
                    $shippingCustomerAddressId = false; //$shippingAddress['address_id'];
                } else {
                    $res = $checkout->saveCheckoutMethod(Mage_Sales_Model_Quote::CHECKOUT_METHOD_GUEST);
                    $billingCustomerAddressId = false;
                    $shippingCustomerAddressId = false;
                }
            }

            try {
                $billingRes = $checkout->saveBilling($billingAddress, $billingCustomerAddressId);
                if (is_array($billingRes) && isset($billingRes['error'])) {
                    $result['success'] = false;
                    $result['error'] = true;
                    $result['error_messages'] = Mage::helper('checkout')->__($billingRes['message']);
                    $this->getResponse()->setBody(Zend_Json::encode($result));
                    return;
                }
                if (!$checkout->getQuote()->isVirtual()) {
                    $result['is_virtual'] = 0;
                    $shippingRes = $checkout->saveShipping($shippingAddress, $shippingCustomerAddressId);
                    if (is_array($shippingRes) && isset($shippingRes['error'])) {
                        $result['success'] = false;
                        $result['error'] = true;

                        $result['error_messages'] = Mage::helper('checkout')->__($shippingRes['message']);
                        $this->getResponse()->setBody(Zend_Json::encode($result));
                        return;
                    }

                    $shippingMethodRes = $checkout->saveShippingMethod($shippingMethod);
                    if (is_array($shippingMethodRes) && isset($shippingMethodRes['error'])) {
                        $result['success'] = false;
                        $result['error'] = true;
                        $result['error_messages'] = Mage::helper('checkout')->__($shippingMethodRes['message']);
                        $this->getResponse()->setBody(Zend_Json::encode($result));
                        return;
                    }
                } else {
                    $result['is_virtual'] = 1;
                }
                $paymentRes = $checkout->savePayment($paymentMethod);
                if (is_array($paymentRes) && isset($paymentRes['error'])) {
                    $result['success'] = false;
                    $result['error'] = true;
                    $result['error_messages'] = Mage::helper('checkout')->__($paymentRes['message']);
                    $this->getResponse()->setBody(Zend_Json::encode($result));
                    return;
                }
            } catch (Exception $e) {
                $result['success'] = false;
                $result['error'] = true;
                $result['error_messages'] = Mage::helper('checkout')->__($e->getMessage());
                $this->getResponse()->setBody(Zend_Json::encode($result));
                return;
            }

            if ($paymentMethod['method'] && $paymentMethod['method'] == 'paypal_express' && (trim($opCheckoutOrderComment) != '')) {
                Mage::getSingleton('checkout/session')->setOpcheckoutOrderComment($opCheckoutOrderComment);
            }
            if ($paymentMethod['method'] && $paymentMethod['method'] == 'paypal_express' && (trim($deliverydate) != '')) {
                Mage::getSingleton('checkout/session')->setShippingArrivalDate($deliverydate);
            }
            if ($paymentMethod['method'] && $paymentMethod['method'] == 'paypal_express' && ($giftmessage) != '') {
                Mage::getSingleton('checkout/session')->setData('opgiftmessage', $giftmessage);
            }
//            newsletter subscription

            try {
                if ($this->getRequest()->getParam('subscription_newsletter')) {
                    if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                        Mage::getSingleton('customer/session')->getCustomer()
                                ->setStoreId(Mage::app()->getStore()->getId())
                                ->setIsSubscribed((boolean) $this->getRequest()->getParam('subscription_newsletter'))
                                ->save();
                    } else {
                        $subscriber = Mage::getSingleton('newsletter/subscriber');
                        $subscriber->subscribe($billingAddress['email']);
                    }
                }
            } catch (Exception $e) {
                Mage::log($e->getMessage());
            }
            //redirection to paypal express
            $redirectUrl = $this->getOnepage()->getQuote()->getPayment()->getCheckoutRedirectUrl();
            if ($redirectUrl) {
                $result['redirect'] = $redirectUrl;
                $this->getResponse()->setBody(Zend_Json::encode($result));
                return;
            }
            $result = array();
            try {
                if ($requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds()) {
                    $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                    if ($diff = array_diff($requiredAgreements, $postedAgreements)) {
                        $result['success'] = false;
                        $result['error'] = true;
                        $result['error_messages'] = $this->__('Please agree to all Terms and Conditions before placing the order.');
                        $this->getResponse()->setBody(Zend_Json::encode($result));
                        return;
                    }
                }
                if ($data = $this->getRequest()->getPost('payment', false)) {
                    $this->getOnepage()->getQuote()->getPayment()->importData($data);
                }
                $this->getOnepage()->saveOrder();
                $redirectUrl = $this->getOnepage()->getCheckout()->getRedirectUrl();
                $result['success'] = true;
                $result['error'] = false;
            } catch (Mage_Core_Exception $e) {
                Mage::logException($e);
                Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
                $result['success'] = false;
                $result['error'] = true;
                $result['error_messages'] = $e->getMessage();

                if ($gotoSection = $this->getOnepage()->getCheckout()->getGotoSection()) {
                    $result['goto_section'] = $gotoSection;
                    $this->getOnepage()->getCheckout()->setGotoSection(null);
                }

                if ($updateSection = $this->getOnepage()->getCheckout()->getUpdateSection()) {
                    if (isset($this->_sectionUpdateFunctions[$updateSection])) {
                        $updateSectionFunction = $this->_sectionUpdateFunctions[$updateSection];
                        $result['update_section'] = array(
                            'name' => $updateSection,
                            'html' => $this->$updateSectionFunction()
                        );
                    }
                    $this->getOnepage()->getCheckout()->setUpdateSection(null);
                }

                $this->getOnepage()->getQuote()->save();
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
                $result['success'] = false;
                $result['error'] = true;
                $result['error_messages'] = $this->__('There was an error processing your order. Please contact us or try again later.');
                $this->getOnepage()->getQuote()->save();
            }

            /**
             * when there is redirect to third party, we don't want to save order yet.
             * we will save the order in return action.
             */
            if (isset($redirectUrl)) {
                $result['redirect'] = $redirectUrl;
            }

            $this->getResponse()->setBody(Zend_Json::encode($result));
            return;
        } else {
            $this->_redirect('checkout/onepage/index');
        }
    }

    public function ajaxloginAction() {
        $username = $this->getRequest()->getPost('opcheckout_username', false);
        $password = $this->getRequest()->getPost('opcheckout_password', false);
        $session = Mage::getSingleton('customer/session');
        $result = array(
            'success' => false
        );
        if ($username && $password) {
            try {
                $session->login($username, $password);
            } catch (Exception $e) {
                $result['error'] = $e->getMessage();
            }
            if (!isset($result['error'])) {
                $result['success'] = true;
            }
        } else {
            $result['error'] = $this->__('Please enter a valid username and password.');
        }
        $this->getResponse()->setBody(Zend_Json::encode($result));
        return;
    }

    public function ajaxforgotpasswordpostAction() {
        $email = (string) $this->getRequest()->getPost('opcheckout_forgotpassword_email');
        $result = array('success' => false);
        if ($email) {
            if (!Zend_Validate::is($email, 'EmailAddress')) {
                $result['error'] = $this->__('Invalid email address.');
                $this->getResponse()->setBody(Zend_Json::encode($result));
                return;
            }

            /** @var $customer Mage_Customer_Model_Customer */
            $customer = Mage::getModel('customer/customer')
                    ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                    ->loadByEmail($email);

            if ($customer->getId()) {
                try {
                    $newResetPasswordLinkToken = Mage::helper('customer')->generateResetPasswordLinkToken();
                    $customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
                    $customer->sendPasswordResetConfirmationEmail();
                } catch (Exception $exception) {
                    $result['error'] = $exception->getMessage();
                    $this->getResponse()->setBody(Zend_Json::encode($result));
                    return;
                }
            }
            $result['success'] = true;
            $result['message'] = Mage::helper('customer')->__('If there is an account associated with %s you will receive an email with a link to reset your password.', Mage::helper('customer')->htmlEscape($email));
            $this->getResponse()->setBody(Zend_Json::encode($result));
            return;
        } else {
            $result['error'] = $this->__('Please enter your email.');
            $this->getResponse()->setBody(Zend_Json::encode($result));
            return;
        }
    }

    /*
     * Discount Coupon Action.. 
     */

    function couponAction() {
        $quote = $this->_getOnepage()->getQuote();
        $couponCode = (string) $this->getRequest()->getParam('code');
        if ($this->getRequest()->getParam('remove') == 1) {
            $couponCode = '';
        }
        $response = array(
            'success' => false,
            'error' => false,
            'message' => false,
        );
        try {
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $quote->setCouponCode(strlen($couponCode) ? $couponCode : '')
                    ->collectTotals()
                    ->save();
            if ($couponCode) {
                if ($couponCode == $quote->getCouponCode()) {
                    $response['success'] = true;
                    $response['message'] = $this->__('Coupon code "%s" was applied successfully.', Mage::helper('core')->htmlEscape($couponCode));
                } else {
                    $response['success'] = false;
                    $response['error'] = true;
                    $response['message'] = $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode));
                }
            } else {
                $response['success'] = true;
                $response['message'] = $this->__('Coupon code was canceled successfully.');
            }
        } catch (Mage_Core_Exception $e) {
            $response['success'] = false;
            $response['error'] = true;
            $response['message'] = $e->getMessage();
        } catch (Exception $e) {
            $response['success'] = false;
            $response['error'] = true;
            $response['message'] = $this->__('Can not apply coupon code.');
        }
//        $html = $this->getLayout()
//        ->createBlock('opcheckout/onepage_shipping_method_available')
//        ->setTemplate('opcheckout/onepage/shipping_method.phtml')
//        ->toHtml();
//        $response['shipping_method'] = $html;
        $this->getResponse()->setBody(Zend_Json::encode($response));
    }

    protected function _getOnepage() {
        return Mage::getSingleton('checkout/type_onepage');
    }

    protected function _canShowForUnregisteredUsers() {
        return Mage::helper('opcheckout')->isActive();
    }

}