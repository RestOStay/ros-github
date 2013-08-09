<?php

/**
 * @ Author     : Apptha team
 * @package     : Apptha_Airhotels
 * @copyright   : Copyright (c) 2011 (www.apptha.com)
 * @license     : http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @Modified Date:June 2012
 */
class Apptha_Airhotels_Model_Airhotels extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();

        $this->_init('airhotels/airhotels');
    }

    /**
     * updateproperty
     * @param $post
     * @return boolean
     */
    public function updateproperty($post) {
        $amenity = array();
        $amenity = implode(",", $post['amenity']);
        $amenity = str_replace(" ", "", $amenity);
        //Update product details

        $product = Mage::getModel('catalog/product')->load($post['id']);
        if ($product->getId()) {
            $product->setPropertytype($post['proptype'])
                    ->setPrivacy($post['privacy'])
                    ->setName($post['name'])
                    ->setDescription($post['desc'])
                    ->setShortDescription($post['sdesc'])
                    ->setAmenity($amenity)
                    ->setAccomodates($post['accomodate'])
                    ->setTotalrooms($post['room'])
                    ->setBedtype($post['bedtype'])
                    ->setPets($post['pets'])
                    ->setPrice($post['price'])
                    ->setpropertyadd($post['address'])
                    ->setCity($post['city'])
                    ->setState($post['state'])
                    ->setCountry($post['propcountry'])
                    ->setMaplocation($post['map'])
                    ->setMetaTitle($post['meta_title'])//Meta title
                    ->setMetaKeyword($post['meta_keyword'])//Meta keywords
                    ->setMetaDescription($post['meta_description'])//Meta description
                    ->setCancelpolicy($post['cancelpolicy']);
            $product->save();
            return true;
        } else {

            return false;
        }
    }

    /**
     *
     * @param array $_FILES
     * @param int $entity_id
     * @return boolean
     */
    public function imageupload($FILES, $entity_id) {

        $mediagalleryId = Mage::helper('airhotels')->getmediagallery(); //get attribute id for image
        $baseimageId = Mage::helper('airhotels')->getbaseimage(); //get attribute id for base_image
        $smallimageId = Mage::helper('airhotels')->getsmallimage(); //get attribute id for small_image
        $thumbimageId = Mage::helper('airhotels')->getthumbimage(); //get attribute id for thumb_image

        $write = Mage::helper('airhotels')->getWRAdapter(); //get write object
        $tPrefix = (string) Mage::getConfig()->getTablePrefix(); //table prefix code
        $catalog_gallery_value_table = $tPrefix . 'catalog_product_entity_media_gallery_value';
        $catalog_gallery_table = $tPrefix . 'catalog_product_entity_media_gallery';

        $PropertyImage = str_replace(' ', '_', strtolower($FILES['uploadfile']['name']));
        $PropertyImage = str_replace('(', '_', $PropertyImage);
        $PropertyImage = str_replace(')', '_', $PropertyImage);
        $splitextension = explode(".", $PropertyImage);
        $tempImageName = "";
        for ($i = 0; $i < count($splitextension) - 1; $i++) {
            $tempImageName .= $splitextension[$i];
        }
        $PropertyImage = $tempImageName . $entity_id . "_" . rand(0, 100000) . "." . $splitextension[count($splitextension) - 1];
        $magePath = str_split($PropertyImage);
        if ($magePath[1] == '') {
            $magePath[1] = '_';
        }
        $imagepath = $magePath[0] . '/' . $magePath[1] . '/' . $PropertyImage;

        if (isset($FILES['uploadfile']['name']) && $FILES['uploadfile']['name'] != '') {
            try {
                $uploader = new Varien_File_Uploader('uploadfile'); // creating the new object
                $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png')); //allowed extensions for uploaded image
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);
                //file path to store the property image
                $path = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'product' . DS . $magePath[0] . DS . $magePath[1] . DS;
                $pathThumbRoot = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'product' . DS . 'thumbs';
                $pathThumbRoot1 = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'product' . DS . 'thumbs' . DS . $magePath[0];
                $pathThumbRoot2 = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'product' . DS . 'thumbs' . DS . $magePath[0] . DS . $magePath[1] . DS;
                $uploader->save($path, $PropertyImage);
                $this->createThumbs($path, $pathThumb, $PropertyImage, $pathThumbRoot, $pathThumbRoot1, $pathThumbRoot2);
            } catch (Exception $e) {
                
            }

            $write->query("Insert into $catalog_gallery_table (attribute_id,entity_id,value) values ($mediagalleryId,$entity_id,'" . $imagepath . "')");
            return;
        }
    }

    /*
     * @param array $post
     * album update
     */

    public function albumupdate($post) {
        $path = $post['album_path'];
        $entity_id = $post['entity_id'];
        $storeId = Mage::app()->getStore()->getId();
        $product = Mage::getModel('catalog/product')->load($entity_id);
        $product->setStoreID($storeId);
        $product->setThumbnail($path)
                ->setImage($path)
                ->setSmallImage($path)
                ->save();

        return;
    }

    /**
     * To verify the booking dates are available and 
     * show the subtotal excluding processing fee .If not 
     * it show the message "Date Not Available"
     * 
     * @return int $subtotal 
     * 
     */
    public function checkavail() {
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('read');
        $tPrefix = (string) Mage::getConfig()->getTablePrefix();
        $booking_table = $tPrefix . 'airhotels_booking';
        $orderItemTable = $tPrefix . 'sales_flat_order';
        $dealstatus[0] = "processing";
        $dealstatus[1] = "complete";
        $from = $_GET["from"];
        $to = $_GET["to"];
        $productid = $_GET["productid"];
        $price = $_GET["price"];

        // start of client issue fixing
        $to = strtotime($to);
        $to = strtotime('-1 day', $to);
        $to = $date = date('m/d/Y', $to);
        // end of client issue

        $calendarDate = $this->dateVerfiy($productid, $from, $to);
        $booked = $this->getDays(count($calendarDate[1]), $calendarDate[1]);
        $notavail = $this->getDays(count($calendarDate[2]), $calendarDate[2]);
        $booked = array_unique($booked);
        $notavail = array_unique($notavail);
        foreach ($calendarDate as $avail) {
            $available = $avail;
            foreach ($available as $availPrice) {
                $avMonth = $availPrice[2];
                $avdays = explode(",", $availPrice[1]);
                for ($avday = 0; $avday < count($avdays); $avday++) {
                    $spDay = (int) $avdays[$avday];
                    $avPrice[$avMonth][$spDay] = $availPrice[3];
                }
                $avday = 0;
            }
            break;
        }

        $currencySymbol = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
        $diff = abs(strtotime($to) - strtotime($from));
        $days = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));
        $subtotal = $days * $price;

        /* checking date */
        $date_range = $read->select()
                ->from(array('ct' => $booking_table), array('ct.entity_id', 'ct.fromdate', 'ct.todate', 'ct.order_id', 'ct.order_item_id'))
                ->join(array('pei' => $orderItemTable), 'pei.entity_id = ct.order_item_id', array())
                ->where('ct.entity_id =?', $productid)
                ->where('pei.status in (?)', $dealstatus);

        $range = $read->fetchAll($date_range);

        $count = count($range);
        $days = array();
        $day = 86400; // Day in seconds  

        for ($i = 0; $i <= $count - 1; $i++) {
            $fromdate = $range[$i]['fromdate'];
            $todate = $range[$i]['todate'];
            $start = $fromdate;
            $end = $todate;
            $sTime = strtotime($start); // Start as time  
            $eTime = strtotime($end); // End as time  
            $numDays = round(($eTime - $sTime) / $day) + 1;
            // Get days  
            for ($d = 0; $d < $numDays; $d++) {
                $days[] = date('m/d/Y', ($sTime + ($d * $day)));
            }
        }

        $Incr = 0;
        $sTime1 = strtotime($from); // Start as time  
        $eTime1 = strtotime($to); // End as time  
        $numDay1 = round(($eTime1 - $sTime1) / $day) + 1;
        // Get days  
        for ($d1 = 0; $d1 < $numDay1; $d1++) {
            $daysIn = date('m/d/Y', ($sTime1 + ($d1 * $day)));
            $dIn = date('d', ($sTime1 + ($d1 * $day)));
            if (in_array($daysIn, $days) || in_array($dIn, $booked) || in_array($dIn, $notavail)) {
                $Incr = $Incr + 1;
                break;
            }
        }
        $total = 0;
        $pFrom = strtotime($from); // Start as time  
        $pTo = strtotime($to); // End as time  

        // start of client issue fixing
        $pDay = round(($pTo - $pFrom) / $day)+1;
        // end of client issue

        // start of orginial calculation
         //$pDay = round(($pTo - $pFrom) / $day)+1;
         //end of orginal calculation

        for ($pr = 0; $pr < $pDay; $pr++) {
            $pIn = date('d', ($pFrom + ($pr * $day)));
            $pIn = (int) $pIn;
            $month = date('n', ($pFrom + ($pr * $day)));
            $av[$month][$pIn] = $price;
        }

        if ($Incr != 0) {
            $availability_to = "no";
        }
        $date1 = strtotime($from);
        $date2 = strtotime($to);
        $dateDiff = $date2 - $date1;
        $no_of_night = $dateDiff / (24 * 60 * 60);

        if ($availability_to == "no") {
            echo 'Dates are not available <br> refer to calendar ';
        } else {

            foreach ($av as $key => $av1) {
                foreach ($av1 as $avkey => $av2) {
                    if ($avPrice[$key][$avkey] != '') {
                        $total = $total + $avPrice[$key][$avkey];
                    } else {

                        $total = $total + $av[$key][$avkey];
                    }
                }
            }
            if ($total != 0) {

                $subtotal = $total;
            }
            $config = Mage::getStoreConfig('airhotels/custom_group');
            $serviceFee = number_format(($subtotal / 100) * ($config["airhotels_servicetax"]), 2);
            $subtotal = Mage::helper('directory')->currencyConvert($subtotal, Mage::app()->getStore()->getBaseCurrencyCode(), Mage::app()->getStore()->getCurrentCurrencyCode());
            $serviceFee = Mage::helper('directory')->currencyConvert($serviceFee, Mage::app()->getStore()->getBaseCurrencyCode(), Mage::app()->getStore()->getCurrentCurrencyCode());

            echo "<p class='subtotal'>Subtotal
                    </p>
                    <h2 class='bigTotal'>" . $currencySymbol . $subtotal . "</h2> <input type='hidden' id='subtotal_days' value = '$days'> <input type='hidden' id='subtotal_amt' value = '$subtotal'>";
            echo '<p class="subtotal" style="color:#2C7ED1;font-size:10px;padding-bottom:10px;">(* Exclude processing fee ' . $currencySymbol . $serviceFee . ")
                        <input type='hidden' id='serviceFee' name='serviceFee' value='" . $serviceFee . "' />
                        </p>

                    <div class='clear'></div>
                    ";
        }
    }

    public function getdate($productid) {

        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_write');
        $tPrefix = (string) Mage::getConfig()->getTablePrefix();
        $booking_table = $tPrefix . 'airhotels_booking';
        $orderItemTable = $tPrefix . 'sales_flat_order';
        $dealstatus[0] = "processing";
        $dealstatus[1] = "complete";
        $date_range = $read->select()
                ->from(array('ct' => $booking_table), array('ct.fromdate', 'ct.todate'))
                ->join(array('pei' => $orderItemTable), 'pei.entity_id = ct.order_item_id', array())
                ->where('ct.entity_id =?', $productid)
                ->where('pei.status in (?)', $dealstatus);

        $range = $read->fetchAll($date_range);
        $count = count($range);
        for ($i = 0; $i <= $count; $i++) {
            $fromdate = $range[$i][fromdate];
            $todate = $range[$i][todate];
            if ($fromdate < $todate) {
                $dates_range[] = date('Y-n-j', strtotime($fromdate));
                $date1 = strtotime($fromdate);
                $date2 = strtotime($todate);
                while ($date1 != $date2) {
                    $date1 = mktime(0, 0, 0, date("m", $date1), date("d", $date1) + 1, date("Y", $date1));
                    $dates_range[] = date('Y-n-j', $date1);
                }
            }
        }

        return $dates_range;
    }

    public function status($status, $pId) {
        $product = Mage::getModel('catalog/product')->load($pId);
        $storeId = Mage::app()->getStore()->getId();
        if ($status == 0) {
            $status == 2;
        }
        $product->setStoreID($storeId)
                ->setStatus($status)//1 = Enabled 2 = Disabled
                ->save();
        return $status;
    }

    public function createThumbs($pathToImages, $pathToThumbs, $fname, $pathThumbRoot, $pathThumbRoot1, $pathThumbRoot2) {
        $thumbWidth = 100;
        if (!is_dir($pathThumbRoot)) {
            mkdir($pathThumbRoot);
        }

        if (!is_dir($pathThumbRoot1)) {
            mkdir($pathThumbRoot1);
        }

        if (!is_dir($pathThumbRoot2)) {
            mkdir($pathThumbRoot2);
        }

        $dir = opendir($pathToImages);
        $info = pathinfo($pathToImages . $fname);
        if (strtolower($info['extension']) == 'jpg' || strtolower($info['extension']) == 'jpeg') {
            // load image and get image size
            $img = imagecreatefromjpeg("{$pathToImages}{$fname}");
        } elseif (strtolower($info['extension']) == 'png') {
            $img = imagecreatefrompng("{$pathToImages}{$fname}");
        } elseif (strtolower($info['extension']) == 'gif') {
            $img = imagecreatefromgif("{$pathToImages}{$fname}");
        }
        $width = imagesx($img);
        $height = imagesy($img);

        // calculate thumbnail size
        $new_width = $thumbWidth;
        $new_height = floor($height * ( $thumbWidth / $width ));

        // create a new temporary image
        $tmp_img = imagecreatetruecolor($new_width, $new_height);

        // copy and resize old image into new image
        imagecopyresized($tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        if (strtolower($info['extension']) == 'jpg' || strtolower($info['extension']) == 'jpeg') {
            // save thumbnail into a file
            imagejpeg($tmp_img, "{$pathThumbRoot2}{$fname}");
        } elseif (strtolower($info['extension']) == 'png') {
            imagepng($tmp_img, "{$pathThumbRoot2}{$fname}");
        } elseif (strtolower($info['extension']) == 'gif') {
            imagegif($tmp_img, "{$pathThumbRoot2}{$fname}");
        }
        //}
        // close the directory
        closedir($dir);
    }

    public function review($status, $reviewId) {
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tPrefix = (string) Mage::getConfig()->getTablePrefix();
        $reviewtable = $tPrefix . 'review';
        // now $write is an instance of Zend_Db_Adapter_Abstract
        $write->query("update $reviewtable set status_id = '$status' where review_id = '$reviewId'  ");
        return $status;
    }

    public function removeImage($imageId, $entityId) {
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        // now $write is an instance of Zend_Db_Adapter_Abstract
        $tPrefix = (string) Mage::getConfig()->getTablePrefix();
        $catalog_product_entity_media_gallery = $tPrefix . 'catalog_product_entity_media_gallery';

        $selectResult = "SELECT `value` FROM $catalog_product_entity_media_gallery WHERE value_id='$imageId' LIMIT 1 ";
        $data = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($selectResult);
        $imageLocation = $data[0]["value"];
        $deleteResult = $write->query("delete from $catalog_product_entity_media_gallery where value_id='$imageId' and entity_id='$entityId'  limit 1  ");
        if ($deleteResult) {
            if (is_file(Mage::getBaseDir() . DS . "media" . DS . "catalog" . DS . "product" . DS . $imageLocation)) {
                unlink(Mage::getBaseDir() . DS . "media" . DS . "catalog" . DS . "product" . DS . $imageLocation);
                unlink(Mage::getBaseDir() . DS . "media" . DS . "catalog" . DS . "product" . DS . "thumbs" . DS . $imageLocation);
            }
        }
        return;
    }

    public function saveInbox($data) {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $cusId = $customer->getId();
        $tPrefix = (string) Mage::getConfig()->getTablePrefix();
        $customer_customer_inbox = $tPrefix . 'airhotels_customer_inbox';
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $deleteResult = $write->query("insert into $customer_customer_inbox (sender_id,receiver_id,product_id,checkin,checkout,guest,message,can_call,timezone,mobileNo) values('$cusId','$data[0]','$data[1]','$data[2]','$data[3]','$data[4]','$data[5]','$data[6]','$data[7]','$data[8]')  ");
        return $deleteResult;
    }

    public function getInboxDetails() {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $CusId = $customer->getId();
        $tPrefix = (string) Mage::getConfig()->getTablePrefix();
        $customer_customer_inbox = $tPrefix . 'airhotels_customer_inbox';
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $selectResult = $write->query("SELECT * FROM $customer_customer_inbox WHERE `receiver_id` = '$CusId' AND `is_receiver_delete` = '0' ORDER BY `created_date` DESC ");
        $result = $selectResult->fetchAll();
        if (empty($result)) {

            $selectResult = $write->query("SELECT * FROM $customer_customer_inbox WHERE `sender_id` = '$CusId' AND `is_reply` = '1' AND `is_sender_delete` = '0' ORDER BY `created_date` DESC ");
            $result = $selectResult->fetchAll();
        }

        return $result;
    }

    public function getOutboxDetails() {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $tPrefix = (string) Mage::getConfig()->getTablePrefix();
        $customer_customer_inbox = $tPrefix . 'airhotels_customer_inbox';
        $CusId = $customer->getId();
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $selectResult = $write->query("select * from $customer_customer_inbox where `sender_id` = '$CusId' and `is_sender_delete` = '0' order by `created_date` desc   ");
        return $selectResult->fetchAll();
    }

    public function getsendMessageDetails($messageid) {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $tPrefix = (string) Mage::getConfig()->getTablePrefix();
        $customer_customer_inbox = $tPrefix . 'airhotels_customer_inbox';

        $CusId = $customer->getId();
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $selectResult = $write->query("select * from $customer_customer_inbox where `sender_id` = '$CusId' and `message_id`  =  '$messageid'  ");
        return $selectResult->fetchAll();
    }

    public function getinboxMessageDetails($messageid) {

        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $tPrefix = (string) Mage::getConfig()->getTablePrefix();
        $customer_customer_inbox = $tPrefix . 'airhotels_customer_inbox';

        $CusId = $customer->getId();
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $selectResult = $write->query("SELECT * FROM $customer_customer_inbox WHERE  (`message_id` = '$messageid') OR (`message_id`  =  '$messageid' AND `is_reply` = '1')");

        return $selectResult->fetchAll();
    }

    public function markAsRead($messageid) {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $tPrefix = (string) Mage::getConfig()->getTablePrefix();
        $customer_customer_inbox = $tPrefix . 'airhotels_customer_inbox';

        $CusId = $customer->getId();
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $selectResult = $write->query("update $customer_customer_inbox set read_flag = '1' where  `message_id`  =  '$messageid'  ");
        return $selectResult;
    }

    public function delelteMessage($messageid, $action) {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $tPrefix = (string) Mage::getConfig()->getTablePrefix();
        $customer_customer_inbox = $tPrefix . 'airhotels_customer_inbox';

        $tempCollection = "";

        if (count($messageid) > 1) {
            $tempCollection = implode(",", $messageid);
        } else {
            $tempCollection = $messageid;
        }

        $CusId = $customer->getId();
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $selectId = $read->fetchAll("select * from $customer_customer_inbox where receiver_id = $CusId ");

        if (!empty($selectId)) {
            $isdelete = 0;
        } else {

            $isdelete = 1;
        }

        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        if ($action == "in") {
            if (count($messageid) > 1) {
                if (!empty($selectId)) {
                    $selectResult = $write->query("update $customer_customer_inbox set is_receiver_delete = '1' where  `message_id` IN ($tempCollection)");
                } else {
                    $selectResult = $write->query("update $customer_customer_inbox set isdelete = $isdelete where  `message_id` IN ($tempCollection)");
                }
            } else {
                // echo "update $customer_customer_inbox set is_receiver_delete = '1'AND isdelete = $isdelete where  `message_id` = $tempCollection[0]";
                if (!empty($selectId)) {
                    $selectResult = $write->query("update $customer_customer_inbox set is_receiver_delete = '1', isdelete = $isdelete  where  `message_id` = $tempCollection[0] ");
                } else {

                    $selectResult = $write->query("update $customer_customer_inbox set isdelete = $isdelete where  `message_id` = $tempCollection[0] ");
                }
            }
        } else {

            if (count($messageid) > 1) {
                $selectResult = $write->query("update $customer_customer_inbox set is_sender_delete = '1' where  `message_id` IN ($tempCollection)");
            } else {

                $selectResult = $write->query("update $customer_customer_inbox set is_sender_delete = '1' where  `message_id` = $tempCollection[0]");
            }
        }
        return $selectResult;
    }

    public function updateProfilePicture() {
        $logoName = "";
        $customer = Mage::getModel("customer/session");
        $email = Mage::getSingleton('customer/session')->getCustomer()->getEmail();
        $tPrefix = (string) Mage::getConfig()->getTablePrefix();
        $customer_photo_table = $tPrefix . 'airhotels_customer_photo';
        $emailpart = explode("@", $email);
        if (isset($_FILES['profilePhoto']['name']) && $_FILES['profilePhoto']['name'] != "") {
            try {
                $uploader = new Varien_File_Uploader("profilePhoto");
                $uploader->setAllowedExtensions(array("jpg", "jpeg", "gif", "png"));
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);
                $path = Mage::getBaseDir("media") . DS . "customer" . DS;
                $ext = explode(".", $_FILES['profilePhoto']['name']);
                $logoName = $emailpart[0] . "." . $ext[count($ext) - 1];
                $path = Mage::getBaseDir("media") . DS . "catalog" . DS . "customer" . DS;
                $pathThumbRoot = Mage::getBaseDir('media') . DS . "catalog" . DS . 'customer' . DS . 'thumbs' . DS;
                $uniqId = time();
                $logoName = $emailpart[0] . '-' . $uniqId . "." . $ext[count($ext) - 1];
                $uploader->save($path, $logoName);

                $this->createThumbs($path, $pathThumb, $logoName, "", "", $pathThumbRoot);
            } catch (Exception $e) {
                
            }
        }
        $customer_id = Mage::getSingleton('customer/session')->getId();
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $selectResult = $write->query("select * from $customer_photo_table where `customer_id` = '$customer_id' ");
        if (count($selectResult->fetchAll()) == 0) {
            $selectResult = $write->query("insert into $customer_photo_table(`customer_id`,`imagename`) values('$customer_id','$logoName')    ");
        } else {
            $selectResult = $write->query("update $customer_photo_table set imagename = '$logoName'  where `customer_id` = '$customer_id' ");
        }
        return true;
    }

    public function getCustomerPictureById($customer_id) {
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tPrefix = (string) Mage::getConfig()->getTablePrefix();
        $customer_photo_table = $tPrefix . 'airhotels_customer_photo';
        $selectResult = $write->query("select * from $customer_photo_table where `customer_id` = '$customer_id' ");
        return $selectResult->fetchAll();
    }

    public function getCustomerRatings($productId) {
        $resource = Mage::getSingleton('core/resource');
        $cn = $resource->getConnection('core_read');
        $sql = "select rt.rating_code,avg(vote.percent) as percent from " . $resource->getTableName('rating_option_vote') . " as vote
               inner join " . $resource->getTableName('rating') . " as rt
               on(vote.rating_id=rt.rating_id)
               inner join " . $resource->getTableName('review') . " as rr
               on(vote.entity_pk_value=rr.entity_pk_value)
                                                                                where rt.entity_id=1 and vote.entity_pk_value='$productId'  and rr.status_id=1
                                                                                group by rt.rating_code";


        $rating = $cn->fetchAll($sql);

        return $rating;
    }

    public function replyMail($messageid, $customerid, $message) {
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tPrefix = (string) Mage::getConfig()->getTablePrefix();
        $airbnb_customer_inbox = $tPrefix . 'airhotels_customer_inbox';

        $selectResult = $write->query("UPDATE $airbnb_customer_inbox SET `is_reply`  = '1' WHERE message_id = '$messageid' AND (sender_id = '$customerid' OR  receiver_id = '$customerid')");

        $customer_reply_mail = $tPrefix . 'airhotels_customer_reply';

        $selectResult = $write->query("INSERT INTO $customer_reply_mail(`message_id`,`customer_id`,`message`) VALUES('$messageid','$customerid','$message')");
        return $selectResult;
    }

    public function getReplyMessages($messageid) {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $tPrefix = (string) Mage::getConfig()->getTablePrefix();
        $customer_customer_reply = $tPrefix . 'airhotels_customer_reply';

        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $selectResult = $write->query("select * from $customer_customer_reply  where message_id = '$messageid'  ");
        return $selectResult->fetchAll();
    }

    public function getReplyCount($messageid) {
        $tPrefix = (string) Mage::getConfig()->getTablePrefix();
        $customer_id = Mage::getSingleton('customer/session')->getId();
        $customer_customer_reply = $tPrefix . 'airhotels_customer_reply';
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $selectResult = $write->query("select count(*) as count from $customer_customer_reply where `message_id` = '$messageid' ");
        $result = $selectResult->fetchAll();
        return $result[0];
    }

    /**
     * function to search property
     */
    public function advanceSearch($data) {

        $state = "";
        $city = "";
        $country = "";
        $address = explode(",", $data["address"]);
        $amount = explode("-", $data["amount"]);
        $upperLimit = $data["upperLimitPrice"];
        $minval = $amount[0];
        $maxval = $amount[1];

        if ($data["checkin"] != "") {
            $fromdate = date("Y-m-d", strtotime($data["checkin"]));
        }
        if ($data["checkout"] != "") {
            //$todate = date("Y-m-d", strtotime($data["checkout"]));
            $todateVal = date("Y-m-d", strtotime($data["checkout"]));
            $dateArr = explode("-", $todateVal);
            $todate = date('Y-m-d', mktime(0, 0, 0, $dateArr[1], $dateArr[2] + 1, $dateArr[0]));
        }
        $collection = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('*')
                ->addFieldToFilter(array(array('attribute' => 'status', 'eq' => '1')));
        $copycollection = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('*')
                ->addFieldToFilter(array(array('attribute' => 'status', 'eq' => '1')));

        $collection->addFieldToFilter('price', array('gteq' => $minval));

        $collection->setOrder('price', 'asc');

//        if ($maxval < $upperLimit) {
            $collection->addFieldToFilter('price', array('lteq' => $maxval));
        //}

        $copycollection->addFieldToFilter('price', array('gteq' => $minval));


//        if ($maxval < $upperLimit) {
            $copycollection->addFieldToFilter('price', array('lteq' => $maxval));
       // }
        if (count($address) == 3) {
            $collection->addFieldToFilter(array(array('attribute' => 'city', 'in' => $address),
                array('attribute' => 'state', 'in' => $address)
                , array('attribute' => 'country', 'in' => $address),
            ));

            $copycollection->addFieldToFilter(array(array('attribute' => 'city', 'in' => $address),
                array('attribute' => 'state', 'in' => $address)
                , array('attribute' => 'country', 'in' => $address),
            ));
        } else if (count($address) == 2) {
            $collection->addFieldToFilter(array(array('attribute' => 'city', 'in' => $address),
                array('attribute' => 'state', 'in' => $address)
                , array('attribute' => 'country', 'in' => $address),
            ));

            $copycollection->addFieldToFilter(array(array('attribute' => 'city', 'in' => $address),
                array('attribute' => 'state', 'in' => $address)
                , array('attribute' => 'country', 'in' => $address),
            ));
        } else if (count($address) == 1) {
            $addressValue = $address[0];
            $collection->addFieldToFilter(array(array('attribute' => 'city', 'like' => "%" . $addressValue . "%"),
                array('attribute' => 'state', 'like' => "%" . $addressValue . "%")
                , array('attribute' => 'country', 'like' => "%" . $addressValue . "%"),
            ));

            $copycollection->addFieldToFilter(array(array('attribute' => 'city', 'like' => "%" . $addressValue . "%"),
                array('attribute' => 'state', 'like' => "%" . $addressValue . "%")
                , array('attribute' => 'country', 'like' => "%" . $addressValue . "%"),
            ));
        }
        if (trim($data["roomtypeval"]) != "") {
            $data["roomtypeval"] = explode(",", $data["roomtypeval"]);
            if (count($data["roomtypeval"]) > 0) {
                $collection->addFieldToFilter('propertytype', array('in' => array($data["roomtypeval"])));
                $copycollection->addFieldToFilter('propertytype', array('in' => array($data["roomtypeval"])));
            }
        }
/*if (trim($data["amenityVal"]) != "") {
            if (count($data["amenityVal"]) > 0) {
                $ame_colelction = explode(',',$data["amenityVal"]);
                $temp_array = array();
               foreach ($ame_colelction as $_value) {
                   array_push($temp_array,array('attribute'=> 'amenity','like' => '%' . $_value . '%'));
               }
               
                $collection->addAttributeToFilter($temp_array);
                $copycollection->addAttributeToFilter($temp_array);
                
            }
        }*/

 if (trim($data["amenityVal"]) != "") {
            if (count($data["amenityVal"]) > 0) {
                $ame_colelction = explode(',',$data["amenityVal"]);

               foreach ($ame_colelction as $_value) {
                // array_push($temp_array,array('attribute'=> 'amenity','like' => '%' . $_value . '%'));
		$collection->addAttributeToFilter('amenity',array('like' => '%' . $_value . '%'));
                $copycollection->addAttributeToFilter('amenity',array('like' => '%' . $_value . '%'));
               }
               /*
                $collection->addAttributeToFilter($temp_array);
                $copycollection->addAttributeToFilter($temp_array);*/

            }
        }
        if ($data["searchguest"] > 0) {
            $collection->addFieldToFilter(array(array('attribute' => 'accomodates', 'gteq' => $data["searchguest"])));
            $copycollection->addFieldToFilter(array(array('attribute' => 'accomodates', 'gteq' => $data["searchguest"])));
        }

        $productFilter = array();
        $count = 0;

        if ($fromdate && $todate) {

            if (count($copycollection)) {
                foreach ($copycollection as $_product) {
                    $availresult = (int) $this->checkAvailableProduct($_product->getId(), $fromdate, $todate);
                    $availresult1 = (int) $this->checkavalidateincal($_product->getId(), $fromdate, $todate);
                    if (!$availresult || !$availresult1) {
                        $productFilter[$count] = $_product->getId();
                        $count++;
                    }
                }
            }
        }

        if (count($productFilter))
            $collection->addFieldToFilter('entity_id', array('nin' => $productFilter));

        $collection->setPage($data["pageno"], 10);

        return $collection;
    }

    /**
     *
     * @param int $productid
     * @param date $fromdate
     * @param date $todate
     * @return boolean 
     */
    public function checkavalidateincal($productid, $fromdate="", $todate="") {
        $myCalendar = $this->dateVerfiy($productid, $fromdate, $todate);
        $blocked = $this->getDays(count($myCalendar[1]), $myCalendar[1]);
        $not_avail = $this->getDays(count($myCalendar[2]), $myCalendar[2]);
        $day = 86400;
        $sTime1 = strtotime($fromdate); // Start as time  
        $eTime1 = strtotime($todate); // End as time  
        $numDay1 = round(($eTime1 - $sTime1) / $day) + 1;
        // Get days  
        for ($d1 = 0; $d1 < $numDay1; $d1++) {
            $daysIn = date('m/d/Y', ($sTime1 + ($d1 * $day)));
            $dIn = date('d', ($sTime1 + ($d1 * $day)));

            if (in_array($dIn, $blocked) || in_array($dIn, $not_avail)) {
                return false;
            }
        }
        return true;
    }

    /**
     *
     * @param int $productid
     * @param date $fromdate
     * @param date $todate
     * @return boolean 
     */
    public function checkAvailableProduct($productid, $fromdate="", $todate = "") {
        $read = Mage::helper('airhotels')->getRDAdapter();
        $tPrefix = (string) Mage::getConfig()->getTablePrefix();

        $From = date('Y-n', strtotime($fromdate)); //Checkin selected date
        $To = date('Y-n', strtotime($todate)); //Checkout selected date
        $dateFrom = explode("-", $From);
        $dateTo = explode("-", $To);


        $booking_table = $tPrefix . 'airhotels_booking';
        $orderItemTable = $tPrefix . 'sales_flat_order';
        $dealstatus[0] = "processing";
        $dealstatus[1] = "complete";
        $date_range = $read->select()
                ->from(array('ct' => $booking_table), array('ct.fromdate', 'ct.todate'))
                ->join(array('pei' => $orderItemTable), 'pei.entity_id = ct.order_item_id', array())
                ->where('ct.entity_id =?', $productid)
                ->where('pei.status in (?)', $dealstatus);

        $range = $read->fetchAll($date_range);

        $count = count($range);
        $collection = array();
        $c = 1;
        for ($i = 0; $i <= $count; $i++) {
            if ($range[$i]["fromdate"] != "") {
                $collection[$c] = $range[$i]["fromdate"];
                $c++;
            }
            if ($range[$i]["todate"]) {
                $collection[$c] = $range[$i]["todate"];
                $c++;
            }
        }

        $availability_from = false;
        $availability_to = false;

        if (count($collection)) {
            for ($i = 1; $i <= count($collection); $i+=2) {
                $myDates = date('Y-n-d', strtotime($collection[$i]));

                $myMonth = explode("-", $myDates);

                if ($this->check_in_range($collection[$i], $collection[$i + 1], $fromdate)) {
                    $availability_from = true;
                }
                if ($this->check_in_range($collection[$i], $collection[$i + 1], $todate)) {
                    $availability_to = true;
                }
                if (($myMonth[1] == $dateFrom[1]) && ($myMonth[0] == $dateTo[0])) {

                    if (array_search($myMonth[2], $blocked) || array_search($myMonth[2], $not_avail)) {

                        $availability_from = false;
                        $availability_to = false;
                    }
                }
            }
            if ($availability_from || $availability_to) {
                return false;
            } else {
                return true;
            }
        }
        return true;
    }
    /**
     *
     * @param date $start_date
     * @param date $end_date
     * @param date $date_from_user
     * @return boolean
     */
    public function check_in_range($start_date, $end_date, $date_from_user) {
        // Convert to timestamp
        $start_ts = strtotime($start_date);
        $end_ts = strtotime($end_date);
        $user_ts = strtotime($date_from_user);
        // Check that user date is between start & end
        return (($user_ts >= $start_ts) && ($user_ts <= $end_ts));
    }
    /**
     *
     * @param int $productid
     * @param date $date
     * @param date $to
     * 
     * @return array $dates_range
     * 
     */
    public function getBlockdate($productid, $date, $to = NULL) {
        $read = Mage::helper('airhotels')->getRDAdapter();
        $tPrefix = (string) Mage::getConfig()->getTablePrefix();
        $blockCalendartable = $tPrefix . 'airhotels_calendar';
        if (isset($_GET["date"])) {
            $dateSplit = explode("__", $_GET["date"]);
            $x = array($dateSplit[0]);
            $year = array($dateSplit[1]);
        } else {
            $x = $date;
            $year = $to;
        }
        $date_range = $read->select()
                ->from(array('ct' => $blockCalendartable), array('ct.book_avail', 'ct.month', 'ct.blockfrom', 'ct.price'))
                ->where('ct.month in (?)', $x)
                ->where('ct.year in (?)', $year)
                ->where('ct.product_id =?', $productid);
        $range = $read->fetchAll($date_range);
        $count = count($range);
        for ($i = 0; $i <= $count; $i++) {
            $bookavail = $range[$i]['book_avail'];
            $fromdate = $range[$i]['blockfrom'];
            $month = $range[$i]['month'];
            $price = $range[$i]['price'];
            if ($bookavail == 1) {
                $dates_available[] = array($bookavail, $fromdate, $month, $price);
            } elseif ($bookavail == 2) {
                $dates_booked[] = array($bookavail, $fromdate);
            } elseif ($bookavail == 3) {
                $dates_notavailable[] = array($bookavail, $fromdate);
            }
        }
        $dates_range = array($dates_available, $dates_booked, $dates_notavailable);
        return $dates_range;
    }

    /**
     *
     * @param int $productid
     * @param date $from
     * @param date $to 
     * 
     * @return array $calendar
     */
    public function dateVerfiy($productid, $from, $to) {
        $productId = $productid; //propertyId 
        $From = date('Y-n', strtotime($from)); //Checkin selected date
        $To = date('Y-n', strtotime($to)); //Checkout selected date
        $dateFrom = explode("-", $From);
        $dateTo = explode("-", $To);
        $month = array_unique(array($dateFrom[1], $dateTo[1]));
        $year = array_unique(array($dateFrom[0], $dateTo[0]));
        $dates_range = $this->getBlockdate($productId, $month, $year);
        return $dates_range;
    }
    /**
     *
     * @param int $count
     * @param array $value
     * @return array $availDays
     */
    public function getDays($count, $value) {
        for ($j = 0; $j < $count; $j++) {
            $availDay[] = $value[$j][1];
        }
        $availDays = explode(",", implode(",", $availDay));
        return $availDays;
    }

    public function getBlockdateBook($productid, $date, $to = NULL) {
        $dates_range = array();
        $read = Mage::helper('airhotels')->getRDAdapter();
        $tPrefix = (string) Mage::getConfig()->getTablePrefix();
        $blockCalendartable = $tPrefix . 'airhotels_booking';
        $orderItemTable = $tPrefix . 'sales_flat_order';
        $dealstatus[0] = "processing";
        $dealstatus[1] = "complete";
        if (isset($_GET["date"])) {
            $dateSplit = explode("__", $_GET["date"]);
            $x = array($dateSplit[0]);
            $year = array($dateSplit[1]);
        } else {
            $x = $date;
            $year = $to;
        }
        $yearVal = $year[0] . '-' . $x[0];
        $query = "SELECT b.fromdate, b.todate from $blockCalendartable as b LEFT JOIN $orderItemTable as t ON t.entity_id = b.order_item_id
                WHERE b.entity_id = '$productid' AND ( b.fromdate LIKE '%" . $yearVal . "%' OR b.todate LIKE '%" . $yearVal . "%') AND (t.status='$dealstatus[1]' OR t.status='$dealstatus[0]')";
        $range = $read->fetchAll($query);
        if (count($range) > 0) {
            foreach ($range as $rangeVal) {
                $dateArr = $this->getDaysBlock($rangeVal['fromdate'], $rangeVal['todate']);
                foreach ($dateArr as $dateArrVal) {
                    $getDateArr = explode('-', $dateArrVal);
                    if ($getDateArr[0] == $year[0] && $getDateArr[1] == $x[0]) {
                        $dates_range[] = $getDateArr[2];
                    }
                }
            }
           
        }
        return $dates_range;
    }

    public function getDaysBlock($sStartDate, $sEndDate) {
        $sStartDate = gmdate("Y-m-d", strtotime($sStartDate));
        $sEndDate = gmdate("Y-m-d", strtotime($sEndDate));
        $aDays[] = $sStartDate;
        $sCurrentDate = $sStartDate;
        while ($sCurrentDate < $sEndDate) {
            $sCurrentDate = gmdate("Y-m-d", strtotime("+1 day", strtotime($sCurrentDate)));
            $aDays[] = $sCurrentDate;
        }
        return $aDays;
    }

}