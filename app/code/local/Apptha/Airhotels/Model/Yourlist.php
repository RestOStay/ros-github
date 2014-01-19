<?php
/**
 * @ Author     : Apptha team
 * @copyright   : Copyright (c) 2011 (www.apptha.com)
 * @license     : http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Apptha_Airhotels_Block_Property_Yourlist extends Mage_Catalog_Block_Product_Abstract
{
    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    /**
     * Host property booking history 
     * 
     * @return array $result;
     */
    public function getBookingHistory() {
        $result = array();
        $fromDate = $this->getRequest()->getParam('from');
        $toDate = $this->getRequest()->getParam('to');
        $session = Mage::getSingleton('customer/session');
        $customerId = $session->getId();
        $read = Mage::helper('airhotels')->getRDAdapter();
        $tPrefix = (string) Mage::getConfig()->getTablePrefix();
        
        if ($fromDate && $toDate) {
            if ($fromDate == "yyyy-mm-dd" && $toDate == "yyyy-mm-dd") {
                $booking_table = $tPrefix . 'airhotels_booking where fromdate >="' . date("Y-m-d", strtotime(date("Y") . "-" . date("m") . "-1")) . '" and todate <= "' . date("Y-m-d", strtotime(date("Y") . "-" . date("m") . "-" . date("t"))) . '" and order_status = "1"';
            } else {
                $booking_table = $tPrefix . 'airhotels_booking where fromdate >="' . $fromDate . '" and todate <= "' . $toDate . '" and order_status = "1"';
            }
        } else {
            $booking_table = $tPrefix . 'airhotels_booking where fromdate >="' . date("Y-m-d", strtotime(date("Y") . "-" . date("m") . "-1")) . '" and todate <= "' . date("Y-m-d", strtotime(date("Y") . "-" . date("m") . "-" . date("t"))) . '" and order_status = "1"';
        }
        $avail = "SELECT *  from  $booking_table";
        $result = $read->fetchAll($avail);
        return $result;
        
    }
    public function getReplyMessages($messageid){
        $result = Mage::getModel('airhotels/airhotels')->getReplyMessages($messageid);
        return $result;
    }
    public function getAdvanceSearchResult(){
        $address = $this->getRequest()->getParam('searchAddress');
        $checkin = $this->getRequest()->getParam('checkin');
        $checkout = $this->getRequest()->getParam('checkout');
        $searchguest = $this->getRequest()->getParam('searchguest');
        $amount = $this->getRequest()->getParam('amount');
        $roomtypeVal = $this->getRequest()->getParam('roomtypeval');
        $amenityval = $this->getRequest()->getParam('amenityval');
        $pageno = $this->getRequest()->getParam('pageno');
        $upperLimitPrice = $this->getRequest()->getParam('upperLimitPrice');
		$Order = $this->getRequest()->getParam('order');
		$Review = $this->getRequest()->getParam('review'); 

        $data = array("address"=>$address,"checkin"=>$checkin,"checkout"=>$checkout,"searchguest"=>$searchguest,"amount"=>$amount,"pageno"=>$pageno,"roomtypeval"=>$roomtypeVal,"amenityVal"=>$amenityval,"upperLimitPrice"=>$upperLimitPrice,"order"=>$Order,"review"=>$Review);
        
        if($data["checkin"] == "mm/dd/yyyy"){
            $data["checkin"] ="";
        }
        if($data["checkout"] == "mm/dd/yyyy"){
            $data["checkout"] ="";
        }
        if(trim($data["address"]) == "e.g. Berlin, Germany"){
            $data["address"] ="";
        }



        $collection = Mage::getModel('airhotels/airhotels')->advanceSearch($data) ; 
      
        return $collection;
    }
    
}
