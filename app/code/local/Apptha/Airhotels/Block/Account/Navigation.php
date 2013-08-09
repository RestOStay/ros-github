<?php
/**
 * @ Author      : Apptha team
 * @package      : Apptha_Airhotels
 * @copyright    : Copyright (c) 2011 (www.apptha.com)
 * @license      : http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @Modified Date:June 2012
 * Description   : Remove links in the customer dashboard
 */
class Apptha_Airhotels_Block_Account_Navigation extends Mage_Customer_Block_Account_Navigation {
	/**
	 * Remove the navigation links in frontend customer dashboard
	 * 
	 * @param array $name
	 * 
	 */
	public function removeLinkByName($name) {
		 
		unset($this->_links[$name]);
	}

}
