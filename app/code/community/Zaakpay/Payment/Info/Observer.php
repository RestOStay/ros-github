<?php

class Zaakpay_Payment_Info_Observer {

    public function check_status($observer) {
        $session = Mage::getSingleton('zaakpay/session');
        $payment = $observer->getPayment();
        $payment_method = $observer->getPayment()->getMethodInstance();
        $orderId = $payment->getOrder()->getIncrementId();
        if ($payment_method->getCode() == 'zaakpay') {
            // check in session cache
            $status = $session->getTransactStatus($orderId);            
            if ($status == null) {
                // get the status here
                $status = $payment_method->checkStatus($payment);
                $session->setTransactStatus($orderId, $status);
            }
            $observer->getTransport()->setData('Latest Zaakpay Status', $status);            
        }
        return $this;
    }

}