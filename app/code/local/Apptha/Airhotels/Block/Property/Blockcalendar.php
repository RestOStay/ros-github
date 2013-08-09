<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Apptha_Airhotels_Block_Property_Blockcalendar extends Mage_Core_Block_Template
{
    public function _prepareLayout()
    {  
        
    	$bundleBlock  = $this->getLayout()->getBlock('property/blockcalendar');
    if ($bundleBlock) {
            $this->setTemplate('airhotels/property/calendar.phtml');
        }
		
    }

}
?>
