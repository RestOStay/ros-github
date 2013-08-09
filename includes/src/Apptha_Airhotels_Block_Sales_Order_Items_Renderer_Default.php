<?php
/**
 * @ Author      : Apptha team
 * @package      : Apptha_Airhotels
 * @copyright    : Copyright (c) 2011 (www.apptha.com)
 * @license      : http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @Modified Date:June 2012
 */
class Apptha_Airhotels_Block_Sales_Order_Items_Renderer_Default extends Mage_Sales_Block_Order_Item_Renderer_Default {
    /**
     * To show the check in, check out , service fee  and Accomodates details 
     * in the order mail and admin side view order
     * 
     * 
     * @return array $result
     */
	
	
	public function getItemOptions() {
		
		$result = array();
		if ($options = $this->getOrderItem()->getProductOptions()) {
					    	
			$productOptions = $this->getOrderItem()->getProductOptions();
			foreach($productOptions as $options1){
				$checkIn = $options1['fromdate'];
				$checkOut =$options1['todate'];
				$rooms = $options1['accomodate'];
				$guests = $options1['serviceFee'];
				
	            break;
			}

			$result = array(    
				array('label' => $this->__('Check In'), 'value' => str_replace('@','/',$checkIn)),
				array('label' => $this->__('Check Out'), 'value' => str_replace('@','/',$checkOut)),
				array('label' => $this->__('Accommodate'), 'value' => $rooms),			
				array('label' => $this->__('Processing Fee'), 'value' => $guests)
			);
		}
		return $result;
	}
}