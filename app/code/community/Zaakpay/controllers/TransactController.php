<?php 

require_once Mage::getBaseDir('lib') . '/Zaakpay/checksum.php';

class Zaakpay_TransactController extends Mage_Core_Controller_Front_Action 
{
    /**
     * Set the quote Id generated in the table into session
     * and add the block of zaakpay form which will submit itself
     * to the zaakpay site
     */
    public function redirectAction() 
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setZaakpayQuoteId($session->getQuoteId());
        $this->getResponse()->setBody($this->getLayout()->createBlock('zaakpay/redirect')->toHtml());
        $session->unsQuoteId();
        $session->unsRedirectUrl();
    }

    public function responseAction() 
    {
        // actual processing
        $postdata = Mage::app()->getRequest()->getPost();
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getZaakpayQuoteId(true));
	$zaakpayConfig = Mage::getStoreConfig('payment/zaakpay');

	// 	Checksum Verification
	//	Proceed only if checksum matches. Else redirect to error page.
	$checksumReceived = $postdata['checksum'];
	$allParamsReceived = Checksum::getAllParams($postdata);

	$checksumCalculated = Checksum::calculateChecksum($zaakpayConfig['secret_key'], $allParamsReceived);
	error_log("Logging response params : " . $allParamsReceived);
	error_log('Logging checksum : ' . $checksumCalculated);
	if ($checksumReceived !== $checksumCalculated) {
		if ($session->getLastRealOrderId()) {
                	$order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
                	if ($order->getId()) {
                    		$order->cancel()->save();
                	}
            	}
		$er = 'Checksum does not match. This response has been compromised. However, transaction might have been successful.';
            	$session->addError($er);
            	$this->_redirect('zaakpay/transact/failure');
		return;
	}


        // success
        if ($this->_validateResponse()) {
            Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();
            // load the order and change the order status
            $zaakpay = Mage::getModel('zaakpay/transact');
            $state = $zaakpay->zaakpaySuccessOrderState();
            $order = Mage::getModel('sales/order')
                ->loadByIncrementId($postdata['orderId'])
                ->setState($state, true);
            // also do something similar to capturing the payment here            
            $payment = $order->getPayment();
            $transaction = Mage::getModel('sales/order_payment_transaction');
            $dummy_txn_id = 'ZP_'.$postdata['orderId'];
            $transaction->setOrderPaymentObject($payment)
                ->setTxnId($dummy_txn_id)
                ->setTxnType(Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH)
                ->setIsClosed(0)
                ->save();
            $order->save();        
            try
			{
			$order->sendNewOrderEmail();
			} catch (Exception $ex) {  }            
			
            $this->_redirect('checkout/onepage/success', array('_secure'=>true));
        } else {
            // failure/cancel
            if ($session->getLastRealOrderId()) {
                $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
                if ($order->getId()) {
                    $order->cancel()->save();
                }
            }
            $er = 'Zaakpay could not process your request because of the error "'.$postdata['responseDescription'] . '"'; 
            $session->addError($er);
            $this->_redirect('zaakpay/transact/failure');
        }
    }

    /**
     * Verify the response coming into the server
     * @return boolean
     */
    protected function _validateResponse() 
    {
        $postdata = Mage::app()->getRequest()->getPost();
        $session = Mage::getSingleton('checkout/session');
        $flag = False;
        error_log('Response Code is ' . $postdata['responseCode']);
	if ((int)$postdata['responseCode'] == 100) {
		$flag = True;
	}
        return $flag;
    }

    public function failureAction() 
    {
        $this->loadLayout();        
        $this->_initLayoutMessages('checkout/session');
        $this->renderLayout();
    }
}
