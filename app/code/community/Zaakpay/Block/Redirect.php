<?php 

class Zaakpay_Block_Redirect extends Mage_Core_Block_Abstract 
{
    /**
     * This will just spit out the html without loading any other magento stuff
     * and the form will be submitted right away.
     */
    protected function _toHtml() 
    {
        $zaakpay = Mage::getModel('zaakpay/transact');
        $fields = $zaakpay->getCheckoutFormFields();
        $form = '<form id="zaakpay_checkout" method="POST" action="' . $zaakpay->getZaakpayTransactAction() . '">';
        foreach($fields as $key => $value) {
            $form .= '<input type="hidden" name="'.$key.'" value="'.Checksum::sanitizedParam($value).'" />'."\n";
            #$form .= '<input type="hidden" name="'.$key.'" value="'.$value.'" />'."\n";
        }
        $form .= '</form>';
        $html = '<html><body>';
        $html .= $this->__('You will be redirected to the Zaakpay website in a few seconds.');
        $html .= $form;
        $html.= '<script type="text/javascript">document.getElementById("zaakpay_checkout").submit();</script>';
        $html.= '</body></html>';
        return $html;
    }
}
