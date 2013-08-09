<?php

class Apptha_Airhotels_Model_Quote_Address_Total_Subtotal extends Mage_Sales_Model_Quote_Address_Total_Subtotal{
   	
   public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);
        $address->setTotalQty(0);
        
        $baseVirtualAmount = $virtualAmount = 0;

        /**
         * Process address items
         */
        $items = $this->_getAddressItems($address);
        foreach ($items as $item) {
            if ($this->_initItem($address, $item) && $item->getQty() > 0) {
                /**
                 * Separatly calculate subtotal only for virtual products
                 */
                
                if ($item->getProduct()->isVirtual()) {
                    
                   $item->setRowTotal(Mage::getSingleton('core/session')->getSubtotal());
                  
                   $item->setBaseRowTotal(Mage::getSingleton('core/session')->getSubtotal());
                    $virtualAmount += $item->getRowTotal();
                    $baseVirtualAmount += $item->getBaseRowTotal();
                }
            }
            else {
                $this->_removeItem($address, $item);
            }
        }
      $address->setSubtotal(Mage::getSingleton('core/session')->getSubtotal()+$address->getFeeAmount());
      $address->setBaseSubtotal(Mage::getSingleton('core/session')->getSubtotal()+$address->getFeeAmount());
     
        /**
         * Initialize grand totals
         */
        Mage::helper('sales')->checkQuoteAmount($address->getQuote(), $address->getSubtotal());
        Mage::helper('sales')->checkQuoteAmount($address->getQuote(), $address->getBaseSubtotal());
        return $this;
    }
 	
    
    
	
}
?>
