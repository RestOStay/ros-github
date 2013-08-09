<?php
/**
 * @ Author     : Apptha team
 * @package     : Apptha_Airhotels
 * @copyright   : Copyright (c) 2011 (www.apptha.com)
 * @license     : http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */

class Apptha_Airhotels_Block_Adminhtml_Airhotels extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_airhotels';
		$this->_blockGroup = 'airhotels';
		$this->_headerText = Mage::helper('airhotels')->__('Booking Information');
		$this->_addButtonLabel = Mage::helper('airhotels')->__('Add Item');
		parent::__construct();
		$this->_removeButton('add');
	}
}