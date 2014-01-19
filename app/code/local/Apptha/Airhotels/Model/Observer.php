<?php
/**
 * @ Author      : Apptha team
 * @package      : Apptha_Airhotels
 * @copyright    : Copyright (c) 2011 (www.apptha.com)
 * @license      : http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @Modified Date:June 2012
 */
class Apptha_Airhotels_Model_Observer
{
    public function booking()
    {
         $fromdate =  Mage::getSingleton('core/session')->getFromdate();

        //$todate = Mage::getSingleton('core/session')->getTodate();

         $todateVal = Mage::getSingleton('core/session')->getTodate();
         $dateArr=explode("-",$todateVal);
         $todate=date('Y-m-d', mktime(0, 0, 0, $dateArr[1], $dateArr[2] - 1, $dateArr[0]));

         if($fromdate && $todate && $fromdate != '' && $todate!= ''){
     	 $accomodate = Mage::getSingleton('core/session')->getAccomodate();
     	 $subtotal = Mage::getSingleton('core/session')->getSubtotal();
     	 $session = Mage::getSingleton('checkout/session');

     	 $productId = "";
     	 $orders = Mage::getModel('sales/order')->getCollection()
     	 ->setOrder('created_at','DESC')
     	 ->setPageSize(1)
     	 ->setCurPage(1);

     	 $order_id = $orders->getFirstItem()->getIncrementId();
     	 $order_item_id = $orders->getFirstItem()->getBillingAddressId();
         $baseCurrency = $orders->getFirstItem()->getBaseCurrencyCode();
         $orderCurrency = $orders->getFirstItem()->getOrderCurrencyCode();
         $address = Mage::getModel('sales/order_address')->load($order_item_id)->getBillingAddress();

     	 foreach ($session->getQuote()->getAllItems() as $item){

     	 	$productId = $item->getProductId();
                 $productData = Mage::getModel('catalog/product')->load($productId);
                $productName = $productData->getName();
                $hostId = $productData->getUserid();

     	 }
     	 $customer = Mage::getSingleton('customer/session')->getCustomer();
     	 $cusId = $customer->getId();
         $buyerEmail = $customer->getEmail();

     	 $resource = Mage::getSingleton('core/resource');
     	 $read = $resource->getConnection('core_write');
     	 $tPrefix = (string) Mage::getConfig()->getTablePrefix();
     	 $booking_table = $tPrefix . 'airhotels_booking';

         $config = Mage::getStoreConfig('airhotels/custom_group');

          //Order processing fee calcualtion
         $serviceFee = ($subtotal/100) * ($config["airhotels_servicetax"] ) ;
         $serviceFee = number_format($serviceFee ,2,'.','' ) ;

         //Commission fee calculation
         $hostFee = ($subtotal/100) * ($config["airhotels_hostfee"] ) ;
         $hostFee = number_format($hostFee,2,'.','' ) ;

         $read->query("Insert into $booking_table (entity_id,product_name,customer_id,customer_email,fromdate,todate,accomodates,host_fee,service_fee,order_id,base_currency_code,order_currency_code,subtotal,order_item_id)
       	 values ($productId,'".$productName."',$cusId,'".$buyerEmail."','".$fromdate."','".$todate."',$accomodate,$hostFee,$serviceFee,$order_id,'".$baseCurrency."','".$orderCurrency."',$subtotal,$order_item_id)");
         Mage::getSingleton('core/session')->setProductID($productId);
         }

    }

     public function catalog_product_save_before($observer) {
        $product = $observer->getProduct();
        $productType = Mage::app()->getRequest()->getParam('type');
        if ($productType == 'property') {
            $customerId = $product->getUserid();
            $customer = Mage::getModel('customer/customer')->load($customerId);
            if ($customer->getId() == '') {
                 $product->setCanSaveCustomOptions(true);
                Mage::throwException(Mage::helper('adminhtml')->__('User id was invalid'));
            }
        }
        return;
    }
	public function block_cal($observer) {
		$order = $observer->getEvent()->getInvoice()->getOrder();
		
		$orderId = $order->getRealOrderId();
		/////////////////////get booking details from airhotels_booking/////////////////////
		$read= Mage::getSingleton('core/resource')->getConnection('core_read');
		$value=$read->query("Select * from airhotels_booking where order_id =".$orderId);
		$rows = $value->fetchAll();
		
		for($i=0;$i<count($rows);$i++)
			{
				$fromdate = $rows[$i]['fromdate'];
				$todate = $rows[$i]['todate'];
				$productId= $rows[$i]['entity_id'];
				$accomodates = 2;
				$price = $rows[$i]['subtotal'];
				$fromD = explode("-",$fromdate);
				$fromY = $fromD[0];
				$fromM = $fromD[1];
				$toD = explode("-",$todate);
				$toY = $toD[0];
				$toM = $toD[1];
				
				if($fromY == $toY)
					{
					$year = $toY;
					
					}
					else
					{
					$year = $fromY.','.$toY;
					}
				if($fromM == $toM)
					{
					$month = $fromM;
					}
					else
					{
					$month = $fromM.','.$toM;
					}
	    $daterange = $this->getAllDatesBetweenTwoDates($fromdate,$todate);
		
		for($j=0;$j<count($daterange);$j++)
			{
				$date = explode('-',$daterange[$j]);
				if($j == 0)
					{
				$dates =  $date[2];
					}
				else
					{
						$dates = $dates.','.$date[2];
					}
			}
		
		 
		 $read->query("Insert into airhotels_calendar (product_id,book_avail,month,year,blockfrom,price)
       	 values ($productId,$accomodates,$month,$year,'".$dates."',$price)");
			}
		
		/////////////////////get booking details from airhotels_booking/////////////////////
		
		
		
	}
	
 public	function getAllDatesBetweenTwoDates($strDateFrom,$strDateTo)
{
     $aryRange=array();

     $iDateFrom=mktime(1,0,0,substr($strDateFrom,5,2),     substr($strDateFrom,8,2),substr($strDateFrom,0,4));
     $iDateTo=mktime(1,0,0,substr($strDateTo,5,2),     substr($strDateTo,8,2),substr($strDateTo,0,4));

     if ($iDateTo>=$iDateFrom)
     {
         array_push($aryRange,date('Y-m-d',$iDateFrom)); // first entry
         while ($iDateFrom<$iDateTo)
         {
             $iDateFrom+=86400; // add 24 hours
             array_push($aryRange,date('Y-m-d',$iDateFrom));
         }
     }
     return $aryRange;
}
}
?>