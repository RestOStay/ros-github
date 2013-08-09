<?php

/**
 * @ Author      : Apptha team
 * @package      : Apptha_Airhotels
 * @copyright    : Copyright (c) 2011 (www.apptha.com)
 * @license      : http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @Modified Date:June 2012
 */
class Apptha_Airhotels_Block_Adminhtml_Airhotels_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    /**
     * Initialize form
     *
     * @return Apptha_Airhotels_Block_Adminhtml_Airhotels_Grid
     */
    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('airhotels_form', array('legend' => Mage::helper('airhotels')->__('Booking Information')));

        //Display the orderId 
        $fieldset->addField('order_id', 'text', array(
            'label' => Mage::helper('airhotels')->__('Order Id'),
            'class' => 'required-entry',
            'required' => true,
            'readonly' => true,
            'name' => 'order_id',
        ));
        //Payment Status 
        $fieldset->addField('status', 'select', array(
            'label' => Mage::helper('airhotels')->__('Status'),
            'name' => 'status',
            'values' => array(
                array(
                    'value' => 2,
                    'label' => Mage::helper('airhotels')->__('Refund To Guest'),
                ),
                array(
                    'value' => 1,
                    'label' => Mage::helper('airhotels')->__('Paid To Hoster'),
                ),
                array(
                    'value' => 0,
                    'label' => Mage::helper('airhotels')->__('Not paid To Hoster'),
                ),
            ),
        ));
        //text editor for admin reply
        $fieldset->addField('message', 'editor', array(
            'name' => 'message',
            'label' => Mage::helper('airhotels')->__('Content'),
            'title' => Mage::helper('airhotels')->__('Content'),
            'style' => 'width:400px; height:200px;',
            'wysiwyg' => false,
            'required' => true,
            
        ));

        if (Mage::getSingleton('adminhtml/session')->getAirhotelsData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getAirhotelsData());
            Mage::getSingleton('adminhtml/session')->setAirhotelsData(null);
        } elseif (Mage::registry('airhotels_data')) {
            $form->setValues(Mage::registry('airhotels_data')->getData());
        }
        return parent::_prepareForm();
    }

}