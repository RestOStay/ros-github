<?php
/**
 *Property booking collection grid block
 * @ Author      : Apptha team
 * @package      : Apptha_Airhotels
 * @copyright    : Copyright (c) 2011 (www.apptha.com)
 * @license      : http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @Modified Date:June 2012
 * Decription    : Display booked property details with the host and service fees for every order order seperately
 * 
 */

class Apptha_Airhotels_Block_Adminhtml_Airhotels_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct() {
		parent::__construct();
		$this->setId('airhotelsGrid');
		$this->setDefaultSort('id');
		$this->setDefaultDir('DESC');
		$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection() {
		//To get booking collection details
		$collection = Mage::getModel('airhotels/airhotels')->getCollection()
		->addFieldToFilter('order_status', '1');
		$this->setCollection($collection);
                
		return parent::_prepareCollection();
	}

	protected function _prepareColumns() {
            $this->addColumn('id', array(
            'header' => Mage::helper('airhotels')->__('Id'),
            'align' => 'left',
            'index' => 'id',
		));
        //propertyid
		$this->addColumn('entity_id', array(
            'header' => Mage::helper('airhotels')->__('Property Id'),
            'align' => 'left',
            'index' => 'entity_id',
		));
       //property name 

                $this->addColumn('property_name',array(
               'header' => Mage::helper('airhotels')->__('Property Name'),
              'align' => 'left',
            'index' => 'product_name',
		));
       //customer email 

            $this->addColumn('customer_email', array(
            'header' => Mage::helper('airhotels')->__('Customer Email'),
            'align' => 'left',
            'index' => 'customer_email',
		));
          
        //checkin date
		$this->addColumn('fromdate', array(
            'header' => Mage::helper('airhotels')->__('From Date'),
            'align' => 'left',
            'type' => 'date',
            'index' => 'fromdate',
		));
        //checkout date
		$this->addColumn('todate', array(
            'header' => Mage::helper('airhotels')->__('To Date'),
            'align' => 'left',
            'type' => 'date',
            'index' => 'todate',
		));
        //No persons had stayed
		$this->addColumn('accomodates', array(
            'header' => Mage::helper('airhotels')->__('Accommodates'),
            'align' => 'left',
            'index' => 'accomodates',
		));
        //Sub Total
	$this->addColumn('subtotal', array(
            'header' => Mage::helper('airhotels')->__('Grand Total'),
            'align' => 'left',
            'index' => 'subtotal',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
            
		));
        //Admin processing fee charged by the admin
		$this->addColumn('servicefee', array(
            'header' => Mage::helper('airhotels')->__('Processing fee'),
            'align' => 'left',
            'index' => 'service_fee',      
            'type'  => 'currency',
            'currency' => 'order_currency_code',
		    
		));
        //Commmission fee to admin
		$this->addColumn('hostfee', array(
            'header' => Mage::helper('airhotels')->__('Commission fee'),
            'align' => 'left',
             'index' => 'host_fee',  
            'type'  => 'currency',
            'currency' => 'order_currency_code',
		       
		));
        //Increment order id
		$this->addColumn('order_id', array(
            'header' => Mage::helper('airhotels')->__('Order Id'),
            'align' => 'left',
            'index' => 'order_id',
		));
        //Status filter
		$this->addColumn('status', array(
            'header' => Mage::helper('airhotels')->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'status',
            'type' => 'options',
            'options' => array(2 => 'Refund To Guest',
                               1 => 'Paid To Hoster',
		               0 => 'Not Paid To Hoster',
		              ),
		));

		$this->addColumn('action',array(
               'header' => Mage::helper('airhotels')->__('Action'),
               'width' => '100',
               'type' => 'action',
               'getter' => 'getId',
               'actions' => array(
		                          array(
                                        'caption' => Mage::helper('airhotels')->__('Edit'),
                                        'url' => array('base' => '*/*/edit'),
                                        'field' => 'id'
                                        )
                                  ),
               'filter' => false,
               'sortable' => false,
               'index' => 'stores',
               'is_system' => true,
         ));
         //Edit Action
         $this->addColumn('action1',array(
                'header' => Mage::helper('airhotels')->__('View Order'),
                'width' => '100',
                'type' => 'action',
                'getter' => 'getOrderItemId',
                'actions' => array(
                                   array(
                                         'caption' => Mage::helper('airhotels')->__('View Order'),
                                         'url' => array('base' => 'adminhtml/sales_order/view'),
                                         'field' => 'order_id'
                                         )
                                   ),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true,
          ));//View order

          $this->addExportType('*/*/exportCsv', Mage::helper('airhotels')->__('CSV'));
          $this->addExportType('*/*/exportXml', Mage::helper('airhotels')->__('XML'));
       
        return parent::_prepareColumns();
        
	}

	protected function _prepareMassaction() {
		$this->setMassactionIdField('id');
		$this->getMassactionBlock()->setFormFieldName('airhotels');

		$this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('airhotels')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('airhotels')->__('Are you sure?')
		));

		$statuses = Mage::getSingleton('airhotels/status')->getOptionArray();

		array_unshift($statuses, array('label' => '', 'value' => ''));
                /**
                 * This was commented not to show in Change status option in the Action
                 */
//		$this->getMassactionBlock()->addItem('status', array(
//            'label' => Mage::helper('airhotels')->__('Change status'),
//            'url' => $this->getUrl('*/*/massStatus', array('_current' => true)),
//            'additional' => array(
//                'visibility' => array(
//                    'name' => 'status',
//                    'type' => 'select',
//                    'class' => 'required-entry',
//                    'label' => Mage::helper('airhotels')->__('Status'),
//                    'values' => $statuses
//		)
//		)
//		));
		return $this;
	}

	public function getRowUrl($row) {
		return $this->getUrl('*/*/edit', array(
            'id' => $row->getId()));
	}
      
        
}