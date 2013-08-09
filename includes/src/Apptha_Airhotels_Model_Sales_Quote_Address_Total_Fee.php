<?php
/**
 * @ Author      : Apptha team
 * @package      : Apptha_Airhotels
 * @copyright    : Copyright (c) 2011 (www.apptha.com)
 * @license      : http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @Modified Date:June 2012
 */

class Apptha_Airhotels_Model_Sales_Quote_Address_Total_Fee extends Mage_Sales_Model_Quote_Address_Total_Abstract{
	
	/**
	 * Collect fee grandtotal
	 *
	 * @param Mage_Sales_Model_Quote_Address $address
	 * @return Apptha_Airhotels_Model_Sales_Quote_Address_Total_Fee
	 */

	public function collect(Mage_Sales_Model_Quote_Address $address)
	{
		parent::collect($address);

		$this->_setAmount(0);
		$this->_setBaseAmount(0);

		$items = $this->_getAddressItems($address);
		if (!count($items)) {
			return $this; //this makes only address type shipping to come through
		}
		$quote = $address->getQuote();
		//Get fee amount
		$exist_amount = $quote->getFeeAmount();
		//get the service fee amount
		$fee = Apptha_Airhotels_Model_Fee::getFee();
		$balance = $fee - $exist_amount;
		//set the balance fee
		$address->setFeeAmount($balance);
		$address->setBaseFeeAmount($balance);

		$quote->setFeeAmount($balance);
                
		//add the service fee to the grandtotal
		$address->setGrandTotal($address->getGrandTotal() + $address->getFeeAmount());
		$address->setBaseGrandTotal($address->getBaseGrandTotal() + $address->getBaseFeeAmount());
			

	}
    /**
     * Retrive the base fee amount 
     * 
     * @param Mage_Sales_Model_Quote_Address $address
     * @return Apptha_Airhotels_Model_Sales_Quote_Address_Total_Fee
     */
	public function fetch(Mage_Sales_Model_Quote_Address $address)
	{
		/**
		 * To retrive the property service fees from table Sales_Flat_Quote_Address
		 *
		 * @return int value
		 */
		$amt = $address->getFeeAmount();
		$address->addTotal(array(
				'code'=>$this->getCode(),
				'title'=>"<span>".Mage::helper('airhotels')->__('Processing Fee')."</span>",
				'value'=> $amt
		));
		return $this;
	}
	/**
	 *To update paypal total 
	 *
	 * @param array $evt
	 *
	 */
	public function updatePaypalTotal($evt) {

		$cart = $evt->getPaypalCart();
		//update subtotal amount with service fees while redirecting to paypal
                $cart->addItem(Mage::helper('airhotels')->__('Processing Fee'), 1,$cart->getSalesEntity()->getFeeAmount(), 'processing fee');
		//$cart->updateTotal(Mage_Paypal_Model_Cart::TOTAL_SUBTOTAL, $cart->getSalesEntity()->getFeeAmount());
	}
}