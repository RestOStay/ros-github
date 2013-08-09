<?php
/**
 * @ Author      : Apptha team
 * @package      : Apptha_Airhotels
 * @copyright    : Copyright (c) 2011 (www.apptha.com)
 * @license      : http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @Modified Date:June 2012
 */

class Apptha_Airhotels_Block_Adminhtml_Airhotels_Edit_Tab_Details extends Mage_Adminhtml_Block_Widget_Form
{
	 /**
     * Initialize form
     *
     * @return Apptha_Airhotels_Block_Adminhtml_Airhotels_Grid
     */
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset('airhotels_form', array('legend'=>Mage::helper('airhotels')->__('Booking Details')));

		//Display the orderId 
                $fieldset->addField('product_name', 'text', array(
            'label' => Mage::helper('airhotels')->__('Property Name :'),
            'readonly' => true,
        ));

        $fieldset->addField('customer_email', 'text', array(
            'label' => Mage::helper('airhotels')->__('Booked Customer Email :'),
            'readonly' => true,
        ));

        $fieldset->addField('fromdate', 'text', array(
            'label' => Mage::helper('airhotels')->__('Check In Date :'),
            'readonly' => true,
        ));

        $fieldset->addField('todate', 'text', array(
            'label' => Mage::helper('airhotels')->__('Check Out Date :'),
            'readonly' => true,
        ));
        $fieldset->addField('accomodates', 'text', array(
            'label' => Mage::helper('airhotels')->__('No of Guest :'),
            'readonly' => true,
        ));
        $fieldset->addField('host_fee', 'text', array(
            'label' => Mage::helper('airhotels')->__('Grand Total :'),
            'readonly' => true,
        ));
        
        $fieldset->addField('service_fee', 'text', array(
            'label' => Mage::helper('airhotels')->__('Processing Fee:'),
            'readonly' => true,
        ));
        $fieldset->addField('subtotal', 'text', array(
            'label' => Mage::helper('airhotels')->__('Host Fee :'),
            'readonly' => true,
        ));

       
        if ( Mage::getSingleton('adminhtml/session')->getAirhotelsData() )
		{
			$form->setValues(Mage::getSingleton('adminhtml/session')->getAirhotelsData());
			Mage::getSingleton('adminhtml/session')->setAirhotelsData(null);
		} elseif ( Mage::registry('airhotels_data') ) {
			$form->setValues(Mage::registry('airhotels_data')->getData());
		}
		return parent::_prepareForm();
	}
}