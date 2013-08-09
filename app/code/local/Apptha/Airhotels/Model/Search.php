<?php
class Apptha_Airhotels_Model_Search extends Mage_Core_Model_Abstract
{
	public function searchproperty($country,$state=NULL,$city=NULL,$fromdate=NULL,$todate=NULL)
	{
            /*City search*/
            
            if($city)
            {
                $SearchCollection = Mage::getModel('airhotels/property')->getpropertycollection()
                                                                       ->addFieldToFilter(array(array('attribute' => 'status', 'eq' => '1')))
                                                                       ->addFieldToFilter(array(array('attribute' => 'city', 'eq' => $city),array('attribute' => 'propertyadd', 'like' => "%".$state."%") ))->addAttributeToSelect('*');
                    /*Calender Availability*/
                    if($fromdate != NULL && $todate != NULL)
                    {
                            foreach ($SearchCollection as $_product)
                            {
                                    $productid = $_product->getId();
                                    $entity_id[] =	$this->checkavail($fromdate,$todate,$productid);
                            }
                            $SearchCollection->addFieldToFilter(array(array('attribute' => 'status', 'nin' => array(2))))	;

                    }
                    /*Calender Availability End*/
                    return $SearchCollection;
            }
            /*City search End*/
            elseif ($city == NULL)
            {
                    /*Country search*/
                $SearchCollection = Mage::getModel('airhotels/property')->getpropertycollection()
                                                                        ->addFieldToFilter(array(array('attribute' => 'status', 'eq' => '1')))
                                                                        ->addFieldToFilter(array(array('attribute' => 'propertyadd', 'like' => "%".$state."%"),array('attribute' => 'country', 'eq' => $country)  ))
                                                                        ->addAttributeToSelect('*');
                                                                        //->addFieldToFilter(array(array('attribute' => 'country', 'eq' => $country)))->addAttributeToSelect('*');
                 /*Calender Availability*/
		if($fromdate != NULL && $todate != NULL){
			foreach ($SearchCollection as $_product)
                        {
                            $productid = $_product->getId();
                            $entity_id[] = $this->checkavail($fromdate,$todate,$productid);
                        }
                        return $SearchCollection->addFieldToFilter('entity_id',$entity_id);
		}
		
		/*Calender Availability End*/
                else{
                        if(count($SearchCollection) == 0)
                        {
                            /*Wild card Search*/
                            $wildcard_text = $country."%";
                            $SearchCollection = Mage::getModel('airhotels/property')->getpropertycollection()
                                        ->addFieldToFilter(array(array('attribute' => 'status', 'eq' => '1')))->addAttributeToSelect('*')
                                        ->addAttributeToFilter('city', array('like' => $wildcard_text));

                            /*Calender Availability*/
                            if($fromdate != NULL && $todate != NULL)
                            {
                                foreach ($SearchCollection as $_product)
                                {
                                    $productid = $_product->getId();
                                    $entity_id[] =	$this->checkavail($fromdate,$todate,$productid);
                                }
                                //print_r($entity_id);
                                    //echo "vxcvxcv"; die;
                                return $SearchCollection->addAttributeToFilter('entity_id', array('nin' => $entity_id));
                            }
                            /*Calender Availability End*/
                            /*Wildcard search End*/
                        }
                        return $SearchCollection;
                    }
	}

    }
    public function checkavail($getfrom,$getto,$productid)
       {
       	$resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('read');
        $tPrefix = (string) Mage::getConfig()->getTablePrefix();
        $booking_table = $tPrefix . 'airhotels_booking';
       
       $from = date("m/d/Y", strtotime($getfrom));
       $to = date("m/d/Y", strtotime($getto));
        

        /* checking date */

            $date_range = $read->select()
                        ->from(array('ct' => $booking_table), array('ct.fromdate','ct.todate','ct.entity_id'));
           $range = $read->fetchAll($date_range);
           $count = count($range);
           for($i = 0; $i <= $count; $i++)
           {
                $fromdate = $range[$i][fromdate];
                $todate = $range[$i][todate] ;
                $productid = $range[$i][entity_id] ;

                $dates_range[]=date('m/d/Y',strtotime($fromdate));
                $date1=strtotime($fromdate);
                $date2=strtotime($todate);
                while ($date1!=$date2)
                {
                    $date1=mktime(0, 0, 0, date("m", $date1), date("d", $date1)+1, date("Y", $date1));
                    $dates_range[]=date('m/d/Y', $date1);
                }
                if(in_array("$from",$dates_range)){
                    $availability_from = "no";
                }
                if(in_array("$to",$dates_range)){
                    $availability_to = "no";
                }
                if($availability_from != "no" || $availability_to == "no" )
                {
                    return $productid;
                }      
    
            }

       }
}
?>