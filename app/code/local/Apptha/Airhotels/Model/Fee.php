<?php
/**
 * @ Author      : Apptha team
 * @package      : Apptha_Airhotels
 * @copyright    : Copyright (c) 2011 (www.apptha.com)
 * @license      : http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @Modified Date:June 2012
 */
class Apptha_Airhotels_Model_Fee extends Varien_Object{

	/**
	 * Get the service fee to show in shopping cart page
	 *
	 *@return int serviceFee
	 */
	public static function getFee(){
		return Mage::getSingleton('core/session')->getServiceFee();
	}
	

}