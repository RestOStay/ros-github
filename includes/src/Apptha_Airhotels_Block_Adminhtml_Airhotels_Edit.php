<?php
/**
 * @ Author      : Apptha team
 * @package      : Apptha_Airhotels
 * @copyright    : Copyright (c) 2011 (www.apptha.com)
 * @license      : http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @Modified Date:June 2012
 */

class Apptha_Airhotels_Block_Adminhtml_Airhotels_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();
		 
		$this->_objectId = 'id';
		$this->_blockGroup = 'airhotels';
		$this->_controller = 'adminhtml_airhotels';

		$this->_updateButton('save', 'label', Mage::helper('airhotels')->__('Save Item'));
		$this->_updateButton('delete', 'label', Mage::helper('airhotels')->__('Delete Item'));
		$this->_removeButton('delete');
		$this->_removeButton('reset');
		$this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
		), -100);

		$this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('airhotels_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'airhotels_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'airhotels_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
	}

	public function getHeaderText()
	{
		if( Mage::registry('airhotels_data') && Mage::registry('airhotels_data')->getId() ) {
			return Mage::helper('airhotels')->__("Hoster Payment Status");
		} else {
			return Mage::helper('airhotels')->__('Add Item');
		}
	}
}