<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Opcheckoutcomment
 *
 * @author Unicode
 */
require_once 'Mage/Adminhtml/Block/Sales/Order/View/Items.php';

class Uni_Opcheckout_Block_Adminhtml_Sales_Order_View_Opcheckoutcomment extends Mage_Adminhtml_Block_Sales_Order_View_Items {

    //put your code here
    public function _toHtml() {
        $html = parent::_toHtml();
        $comment = $this->getOpcheckoutOrderCommentHtml();
        $deliverDate = $this->getDeliveryDateHtml();
        if(Mage::getStoreConfig('opcheckout/order/opcheckout_order_comment')==1){
        return $html . $comment.$deliverDate;
        }
        return $html.$deliverDate;
    }

    public function getOpcheckoutOrderCommentHtml() {
        $comment = $this->getOrder()->getOrderComment();
        $html = '';
        if (Mage::getStoreConfigFlag('opcheckout/order/opcheckout_order_comment') && $comment) {
            $html .= '<div id="customer_comment" class="giftmessage-whole-order-container">
                        <div class="entry-edit">
                            <div class="entry-edit-head">
                                <h4>' . $this->helper('opcheckout')->__('Customer Comment') . '</h4>
                            </div>
                            <fieldset>' . nl2br($this->helper('opcheckout')->htmlEscape($comment)) . '</fieldset>
                        </div>
                      </div>';
        }
        return $html;
    }

    public function getDeliveryDateHtml() {
        $date_format = 'd/M/Y';
        $date_format.=" ,g:i a";
        $ddate = $this->getOrder()->getShippingArrivalDate();
        $formateddate = date ($date_format,strtotime($ddate));
        $html = '';
        if (Mage::getStoreConfigFlag('opcheckout/order/opcheckout_order_deliverydate') == 1 && $ddate != '') {
            $html .= '<div id="delivery_date" class="delivery-whole-order-container">
                        <div class="entry-edit">
                            <div class="entry-edit-head">
                                <h4>' . $this->helper('opcheckout')->__('Deliver Date') . '</h4>
                            </div>
                            <fieldset>' . nl2br($this->helper('opcheckout')->htmlEscape($formateddate)) . '</fieldset>
                        </div>
                      </div>';
        }
        return $html;
    }

}

?>
