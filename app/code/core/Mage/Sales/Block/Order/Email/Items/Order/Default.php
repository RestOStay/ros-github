<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Sales Order Email items default renderer
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Block_Order_Email_Items_Order_Default extends Mage_Core_Block_Template
{
    /**
     * Retrieve current order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return $this->getItem()->getOrder();
    }

    public function getItemOptions()
    {
        $result = array();
        if ($options = $this->getItem()->getProductOptions()) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }
        }

        return $result;
    }
	
	public function getAlldate(){
		//$dateArray = array();
		$oQuote = Mage::getSingleton('checkout/session')->getQuote();
		foreach ( $oQuote->getAllItems() as $_item )
		{
			$orderItemId[] = $_item->getId();
		}
		return $orderItemId;
	}

    public function getValueHtml($value)
    {
        if (is_array($value)) {
            return sprintf('%d', $value['qty']) . ' x ' . $this->htmlEscape($value['title']) . " " . $this->getItem()->getOrder()->formatPrice($value['price']);
        } else {
            return $this->htmlEscape($value);
        }
    }

    public function getSku($item)
    {
        if ($item->getProductOptionByCode('simple_sku'))
            return $item->getProductOptionByCode('simple_sku');
        else
            return $item->getSku();
    }
	
	public function getmyproductOptions($item)
    {
       echo $productOptionid = $item->getItemId();
	   $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
	   $sql = "Select * from sales_flat_order_item where item_id=$productOptionid";
	   $rows = $connection->fetchRow($sql);
	   $productOption = unserialize($rows['product_options']);	
	   echo '<pre>';
	   print_r($productOption);
	   die;
	   $to = str_replace("@",".",$productOption['info_buyRequest']['todate']);
	   $from = str_replace("@",".",$productOption['info_buyRequest']['fromdate']);
	   $to = date('Y-m-d', strtotime(str_replace('.', '/', $to)));
	   $from = date('Y-m-d', strtotime(str_replace('.', '/', $from)));
	   /*$to = date("Y-m-d", strtotime($to) );
	   $to = date('Y-m-d', strtotime($productOption['info_buyRequest']['todate']));
	   $from = date("Y-m-d", strtotime($from) );
	   $from = date('Y-m-d', strtotime($productOption['info_buyRequest']['todate']));*/
	   
	   $date1 = new DateTime($from);
	   $date2 = new Datetime($to);
	   $interval = $date1->diff($date2);
	   $diff = $interval->format('%R%d Night');
	   
	  /* $date1 = new DateTime($productOption['info_buyRequest']['todate']);
	   $date2 = new DateTime($productOption['info_buyRequest']['fromdate']);
	   $diff = $date2->diff($date1)->format("%a");*/
	   
	   $dataArray[] =  str_replace("@","-",$productOption['info_buyRequest']['fromdate']);
	   $dataArray[] =  str_replace("@","-",$productOption['info_buyRequest']['todate']);
	   $dataArray[] =  $diff;//$productOption['info_buyRequest']['accomodate'];
	   $dataArray[] =  $productOption['info_buyRequest']['rooms'];
	   $dataArray[] =  $productOption['info_buyRequest']['accomodate'];
	   $dataArray[] =  $productOption['info_buyRequest']['child'];
	   return $dataArray;
		
    }

    /**
     * Return product additional information block
     *
     * @return Mage_Core_Block_Abstract
     */
    public function getProductAdditionalInformationBlock()
    {
        return $this->getLayout()->getBlock('additional.product.info');
    }
}
