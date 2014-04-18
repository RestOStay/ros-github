<?php 

class Zaakpay_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        // $this->testTransact();
        // $this->testUpdate();
        // $this->testPayment();
        $this->testSession();
    }

    protected function testTransact() {
        $order = Mage::getModel('sales/order')
            ->load(27);
        $config = Mage::getStoreConfig('payment/zaakpay');
        var_dump($config); exit;

        $zaakpay = Mage::getModel('zaakpay/transact');
        $fields = $zaakpay->getCheckoutFormFields();
        $form = '<form id="zaakpay_checkout" method="POST" action="' . $zaakpay->getZaakpayTransactAction() . '">';
        foreach($fields as $key => $value) {
            $form .= '<input type="hidden" name="'.$key.'" value="'.$value.'" />'."\n";
        }
        $form .= '</form>';
        $html = '<html><body>';
        $html .= $this->__('You will be redirected to the Zaakpay website in a few seconds.');
        $html .= $form;
        // $html.= '<script type="text/javascript">document.getElementById("zaakpay_checkout").submit();</script>';
        $html.= '</body></html>';
        echo $html;
        exit;
    }

    protected function testUpdate() {
        $request = Mage::getModel('zaakpay/api_request');
        $request->setZaakpayConfig(Mage::getStoreConfig('payment/zaakpay'))
            ->setUrl('https://api.zaakpay.com/updatetransaction')
            ->addParam('orderId', '100000040')
            ->addParam('updateDesired', '7')
            ->addParam('updateReason', 'Lorem Ipsum')
            ->send();
    }

    protected function testPayment() {
        $order = Mage::getModel('sales/order')
            ->load(27);
        var_dump($order->getPayment()); exit;
    }

    protected function testSession() {
        $session = Mage::getSingleton('zaakpay/session');
        var_dump($session->getTransactStatus('100000082')); exit;        
    }
}