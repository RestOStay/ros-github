<?php
/**
 * @ Author     : Apptha team
 * @package     : Apptha_Airhotels
 * @copyright   : Copyright (c) 2011 (www.apptha.com)
 * @license     : http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Apptha_Airhotels_Block_Adminhtml_Airhotels_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('airhotels_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('airhotels')->__('Booking Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('airhotels')->__('Payment to Hoster'),
          'title'     => Mage::helper('airhotels')->__('Payment to Hoster'),
          'content'   => $this->getLayout()->createBlock('airhotels/adminhtml_airhotels_edit_tab_form')->toHtml(),
          'active'    => Mage::registry('airhotels_data')->getId() ? false : true
      ));
     $this->addTab('form_details', array(
          'label'     => Mage::helper('airhotels')->__('Booking Details'),
          'title'     => Mage::helper('airhotels')->__('Booking Details'),
          'content'   => $this->getLayout()->createBlock('airhotels/adminhtml_airhotels_edit_tab_Details')->toHtml(),
      ));
      return parent::_beforeToHtml();
  }
}