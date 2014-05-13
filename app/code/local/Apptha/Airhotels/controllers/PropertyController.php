<?php

/**
 * @ Author      : Apptha team
 * @package      : Apptha_Airhotels
 * @copyright    : Copyright (c) 2011 (www.apptha.com)
 * @license      : http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @Modified Date:June 2012
 */
class Apptha_Airhotels_PropertyController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * To post the new property
     *
     * @return redirect to gallery page
     */
    public function postAction() {
        $this->loadLayout();
        $this->renderLayout();
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $CusId = $customer->getId();
        $CusEmail = $customer->getEmail();
        $post = $this->getRequest()->getPost();
        $amenity = array();
        $amenity = implode(",", $post['amenity']);
        $amenity = str_replace(" ", "", $amenity);
        $random = rand(1, 100000000000);
        $sku = rand(1, $random);
        $websiteId = Mage::app()->getWebsite()->getId(); //Website Id
        $store_id = Mage::app()->getStore()->getId(); //store Id
		
		// my currency conversion code here //
		if($post['currency'] == 'INR'){
			$currencyrate = Mage::app()->getStore()->getCurrentCurrencyRate(); 
			$price = round(($post['price']/$currencyrate),2);
			$post['price'] = $price;
		}
		/*echo '<pre>';
		print_r($post);
		
		die;*/
		
        if ($post) {

            $product = Mage::getModel('catalog/product');
            // Build the product
            $product->setStoreID($store_id);//Store Id
                   $product ->setTotalrooms($post['room'])//No of roms avaliable
                    ->setSku($sku)//product Sku
                    ->setUserid($CusId)//Customer id
                    ->setAttributeSetId(4)
                    ->setTypeId('property')//product type
                    ->setName($post['name'])//propertyName
                    ->setDescription($post['desc'])//Description
                    ->setShortDescription($post['sdesc'])//shortdescription
                    ->setPrice($post['price']) // Set some price
                    ->setAccomodates($post['accomodate'])//Custom created and assigned attributes
                    ->setHostemail($CusEmail)//host email id
                    ->setpropertyadd($post['address'])// property address
                    ->setAmenity($amenity)//amenity like room service,e.t.c
                    ->setState($post['state'])//property state name
                    ->setCity($post['city'])// property city name
                    ->setCountry($post['propcountry'])//country
					->setPhoneno($post['phoneno'])//country
                    ->setCancelpolicy($post['cancelpolicy'])//regarding to cancelation policy
                    ->setPets($post['pets'])//regaring to pets allowed or not allowed
                    ->setBedtype($post['bedtype'])//bedtype
                    ->setMaplocation($post['map'])//property map location
                    ->setMetaTitle($post['meta_title'])//Meta title
                    ->setMetaKeyword($post['meta_keyword'])//Meta keywords
                    ->setMetaDescription($post['meta_description'])//Meta description
                    ->setPropertytype(array($post['proptype']))//property type
                    ->setPrivacy(array($post['privacy']))//privacy
                    ->setCategoryIds(Mage::app()->getStore()->getRootCategoryId())//Default Category
                    ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)//Visibility in both catalog and search
                    ->setStatus(1)//enable the Status
                    ->setTaxClassId(0) # My default tax class
                    ->setStockData(array(
                        'is_in_stock' => 1,
                        'qty' => 100000
                    ))//Inventory
                    ->setCreatedAt(strtotime('now'))
                    ->setWebsiteIDs(array($websiteId)); //Website id, my is 1 (default frontend)
                try {
            $product->save();
                }
                catch (Exception $ex) {
                    //Handle the error
                }

            Mage::getSingleton('core/session')->addSuccess($this->__('Space created Successfully'));
            $url = Mage::getBaseUrl() . "airhotels/property/image/id/" . $product->getId();

            Mage::app()->getFrontController()->getResponse()->setRedirect($url);
        } else {
            Mage::getSingleton('core/session')->addError($this->__('Error'));
            $this->_redirect('*/*/');
        }
    }

    /**
     * Redirect to the customer login page when you click on the List the new space
     *
     */
    public function formAction() {
        Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::helper('airhotels')->getformUrl());
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {  // if not logged in
            Mage::getSingleton('core/session')->addError($this->__('Login and create your space'));
            $this->_redirectUrl(Mage::helper('customer')->getLoginUrl());
        } else {
            $this->getLayout()->getBlock('head')->setTitle($this->__('List your Space'));
            $this->renderLayout();
        }
    }

    /**
     * Property Edit action
     *
     */
    public function editAction() {
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $id = $customer->getId();
        if ($id) {
            $this->getLayout()->getBlock('head')->setTitle($this->__('Edit your Place'));
            $this->renderLayout();
        } else {
            Mage::getSingleton('core/session')->addError($this->__('You are not currently logged in'));
            $this->_redirectUrl(Mage::helper('customer')->getLoginUrl());
        }
    }

    /**
     * List all the property which are realted to customer
     *
     */
    public function showAction() {
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {  // if not logged in
            $this->_redirectUrl(Mage::helper('customer')->getLoginUrl());
        } else {
            $this->getLayout()->getBlock('head')->setTitle($this->__('My List'));
        }
        $this->renderLayout();
    }

    /**
     * set action url for property images
     * @ update /delete image
     *
     */
    public function imageAction() {
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {  // if not logged in
            $this->_redirectUrl(Mage::helper('customer')->getLoginUrl());
        } else {
            $this->getLayout()->getBlock('head')->setTitle($this->__('My List'));
        }
        $this->renderLayout();
    }

    /**
     * set action for uploading the new property image for existing property
     * @return boolean
     */
    public function imageuploadAction() {
        $this->loadLayout();
        $entity_id = $this->getRequest()->getParam('id');  //property id
        /*
         * @param int $entity_id
         * @param array $_FILES
         */
      
        Mage::getModel('airhotels/airhotels')->imageupload($_FILES, $entity_id);
        $this->renderLayout();
        return;
    }

    /**
     * Property update
     *
     * @return My listing page
     */
    public function updateAction() { 
        $post = $this->getRequest()->getPost();
		//echo "<pre>";print_r($post);die;
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Airhotels'));
        $this->renderLayout();
		
		// my currency conversion code here //
		if($post['currency'] == 'INR'){
			$currencyrate = Mage::app()->getStore()->getCurrentCurrencyRate(); 
			$price = round(($post['price']/$currencyrate),2);
			$post['price'] = $price;
		}
		/*echo '<pre>';
		print_r($post);
		
		die;*/
		
        $result = Mage::getModel('airhotels/airhotels')->updateproperty($post);
        if ($result == 1) {
            Mage::getSingleton('core/session')->addSuccess($this->__('Your place updated successfully'));
            $url = Mage::getBaseUrl() . "airhotels/property/image/id/" . $post["id"];
        } else {
            Mage::getSingleton('core/session')->addError($this->__('Your place not updated successfully'));
            $url = Mage::helper('airhotels')->getediturl() . "id/" . $post["id"];
        }

        Mage::app()->getFrontController()->getResponse()->setRedirect($url);
    }

    /**
     * Proeprty View page
     *
     */
    public function viewAction() {
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('View'));
        $this->renderLayout();
    }

    /**
     * Customer trip history page
     *
     */
    public function yourtripAction() {

        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {  // if not logged in
            $this->_redirectUrl(Mage::helper('customer')->getLoginUrl());
        } else {
            $this->getLayout()->getBlock('head')->setTitle($this->__('Your Trip'));
        }
        $this->renderLayout();
    }

    /**
     * Property search result page
     * 
     */
    public function searchAction() {
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Search List'));
        $this->renderLayout();
    }

    public function infoAction() {
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Info'));
        $this->renderLayout();
    }

    /**
     * To Check the booking dates the property was available or not
     * 
     */
    public function checkavailAction() {
        Mage::getModel('airhotels/airhotels')->checkavail();
    }

    public function calenderAction() {
	
        $productId = $_REQUEST["productid"]; //To
        $dateSplit = explode("__", $_REQUEST["date"]);
        $blockedArray = Mage::getModel('airhotels/airhotels')->getBlockdate($productId, $_REQUEST["date"]);
		/**
         * Assign availiable, blocked and not availiable date
         */
        $avail = $this->getDaysAction(count($blockedArray[0]),$blockedArray[0]);
        $blockedArr = $this->getDaysAction(count($blockedArray[1]),$blockedArray[1]);
        // start new concept for booked date
        $blockedArrayCust = Mage::getModel('airhotels/airhotels')->getBlockdateBook($productId,$_GET["date"]);
        $blocked=array_merge($blockedArr, $blockedArrayCust);
        $selectedArray=array();
		/*echo '<pre>';
		echo $blocked;
		exit;*/
        // end new concept for booked date

        $not_avail = $this->getDaysAction(count($blockedArray[2]),$blockedArray[2]);
        // calender price start
        $spe_avail = $this->getSpecialPriceDaysAction(count($blockedArray[0]),$blockedArray[0]);
        $_sp = array();
        foreach($spe_avail as $key=>$value)
        {
            $avail = explode(",",$key);
            foreach($avail as $_val){
                 $spDay = (int) $_val;
                $_sp[$spDay] = $value;
            }
         }
        //end of calender price
        $x = $dateSplit[0];
        if ($x == "")
            $x = date("n");
        $year = $dateSplit[1];
        $date = strtotime("$year/$x/1");
        $day = date("D", $date);
        $m = date("F", $date);
        $prev_year = $year;
        $next_year = $year;
        $prev_month = intval($x) - 1;
        $next_month = intval($x) + 1;

// if current month is Decembe or January month navigation links have to be updated to point to next / prev years
        if ($x == 12) {
            $next_month = 1;
            $next_year = $year + 1;
        } elseif ($x == 1) {
            $prev_month = 12;
            $prev_year = $year - 1;
        }
        $totaldays = date("t", $date);
        echo "<table border = '1' cellspacing = '0'  bordercolor='blue' cellpadding ='2' class='calend'>
                        <tr class='weekDays'>
                        <th><font size = '2' face = 'tahoma'>Sun</font></th>
                        <th><font size = '2' face = 'tahoma'>Mon</font></th>
                        <th><font size = '2' face = 'tahoma'>Tue</font></th>
                        <th><font size = '2' face = 'tahoma'>Wed</font></th>
                        <th><font size = '2' face = 'tahoma'>Thu</font></th>
                        <th><font size = '2' face = 'tahoma'>Fri</font></th>
                        <th><font size = '2' face = 'tahoma'>Sat</font></th>
                        </tr> ";

        if ($day == "Sun")
            $st = 1;
        if ($day == "Mon")
            $st = 2;
        if ($day == "Tue")
            $st = 3;
        if ($day == "Wed")
            $st = 4;
        if ($day == "Thu")
            $st = 5;
        if ($day == "Fri")
            $st = 6;
        if ($day == "Sat")
            $st = 7;

        if ($st >= 6 && $totaldays == 31) {
            $tl = 42;
        } elseif ($st == 7 && $totaldays == 30) {
            $tl = 42;
        } else {
            $tl = 35;
        }

        $ctr = 1;
        $d = 1;



        for ($i = 1; $i <= $tl; $i++) {

            if ($ctr == 1)
                echo "<tr class='blockcal'>";

            if ($i >= $st && $d <= $totaldays) {
                if (strtotime("$year-$x-$d") < strtotime(date("Y-n-j"))) {
                    echo "<td align='center' class='previous days '><font size = '2' face = 'tahoma'>$d</font></td>";
                } else {

                    if (in_array("$year-$x-$d", $selectedArray)) {
                        echo "<td class='selected days' align='center'><font size = '2' face = 'tahoma'>$d</font></td>";
                    } else {
                        $date = strtotime("$year/$x/$d");
                        $tdDate = 'tdId' . '_' . date("m/d/Y", $date);

                        if (in_array("$d", $blocked)) {
                            echo "<td id=" . $tdDate . " class='normal days " . $d . " ' align='center' style='background-color:red;'><font size = '2' face = 'tahoma'>$d</font></td>";
                        } else if (in_array("$d", $not_avail)) {
                            echo "<td id=" . $tdDate . " class='normal days " . $d . " ' align='center'style='background-color:#F18200;color: black !important;' ><font size = '2' face = 'tahoma'>$d</font></td>";
                        } else if(array_key_exists($d,$_sp)){
                                echo "<td style='padding: 11px 23px;' id=" . $tdDate . " class='normal days " . $d . " ' align='center' ><font size = '2' face = 'tahoma'>$d</font><br><div style='width: 25px;font-size: 1.0em;text-align: right;'>". Mage::app()->getLocale()->currency(Mage::app()->getStore()->
     getCurrentCurrencyCode())->getSymbol()."$_sp[$d]</div></td>";
                            }else{
                                echo "<td id=" . $tdDate . " class='normal days " . $d . " ' align='center' ><font size = '2' face = 'tahoma'>$d</font></td>";
                          }
                        }
                }
                $d++;
            } else {
                echo "<td>&nbsp</td>";
            }

            $ctr++;

            if ($ctr > 7) {
                $ctr = 1;
                echo "</tr>";
            }
        }

        echo '</table>';
    }
    /**
     * Mycalendar layout
     * 
     */
    
   
    public function calendarviewAction() {

        $productId = $_REQUEST["productid"]; //To 
        $dateSplit = explode("__", $_REQUEST["date"]);
        $blockedArray = Mage::getModel('airhotels/airhotels')->getBlockdate($productId, $_REQUEST["date"]);
        /**
         * Assign availiable, blocked and not availiable date 
         */
        $avail = $this->getDaysAction(count($blockedArray[0]),$blockedArray[0]);
        $blockedArr = $this->getDaysAction(count($blockedArray[1]),$blockedArray[1]);
        // start new concept for booked date
        $blockedArrayCust = Mage::getModel('airhotels/airhotels')->getBlockdateBook($productId,$_GET["date"]);
        $blocked=array_merge($blockedArr, $blockedArrayCust);
        // end new concept for booked date

        $not_avail = $this->getDaysAction(count($blockedArray[2]),$blockedArray[2]);
        // calender price start
        $spe_avail = $this->getSpecialPriceDaysAction(count($blockedArray[0]),$blockedArray[0]);
        $_sp = array();
        foreach($spe_avail as $key=>$value)
        {
            $avail = explode(",",$key);
            foreach($avail as $_val){
                 $spDay = (int) $_val;
                $_sp[$spDay] = $value;
            }
         }
        //end of calender price
        $x = $dateSplit[0];
        if ($x == "")
            $x = date("n");
        $year = $dateSplit[1];
        $date = strtotime("$year/$x/1");
        $day = date("D", $date);
        $m = date("F", $date);
        $prev_year = $year;
        $next_year = $year;
        $prev_month = intval($x) - 1;
        $next_month = intval($x) + 1;

// if current month is Decembe or January month navigation links have to be updated to point to next / prev years
        if ($x == 12) {
            $next_month = 1;
            $next_year = $year + 1;
        } elseif ($x == 1) {
            $prev_month = 12;
            $prev_year = $year - 1;
        }
        $totaldays = date("t", $date);
        echo '<a class="pre_grid" href="javascript:void(0);" onclick="ajaxLoadCalendar(\'' . Mage::getBaseUrl() . 'airhotels/property/calendarview/?date=' . $prev_month . '__' . $prev_year . '&productid=' . $productId . '\')" >previous</a>';
        echo '<div class="date_grid">' . date("F, Y", $date) . '</div>';
        echo '<a class="next_grid" href="javascript:void(0);" onclick="ajaxLoadCalendar(\'' . Mage::getBaseUrl() . 'airhotels/property/calendarview/?date=' . $next_month . '__' . $next_year . '&productid=' . $productId . '\')" >next</a>';

        echo "<table border = '1' cellspacing = '0'  bordercolor='blue' cellpadding ='2' class='calend'>
                        <tr class='weekDays'>
                        <th><font size = '2' face = 'tahoma'>Sun</font></th>
                        <th><font size = '2' face = 'tahoma'>Mon</font></th>
                        <th><font size = '2' face = 'tahoma'>Tue</font></th>
                        <th><font size = '2' face = 'tahoma'>Wed</font></th>
                        <th><font size = '2' face = 'tahoma'>Thu</font></th>
                        <th><font size = '2' face = 'tahoma'>Fri</font></th>
                        <th><font size = '2' face = 'tahoma'>Sat</font></th>
                        </tr> ";

        if ($day == "Sun")
            $st = 1;
        if ($day == "Mon")
            $st = 2;
        if ($day == "Tue")
            $st = 3;
        if ($day == "Wed")
            $st = 4;
        if ($day == "Thu")
            $st = 5;
        if ($day == "Fri")
            $st = 6;
        if ($day == "Sat")
            $st = 7;

        if ($st >= 6 && $totaldays == 31) {
            $tl = 42;
        } elseif ($st == 7 && $totaldays == 30) {
            $tl = 42;
        } else {
            $tl = 35;
        }

        $ctr = 1;
        $d = 1;



        for ($i = 1; $i <= $tl; $i++) {

            if ($ctr == 1)
                echo "<tr class='blockcal'>";

            if ($i >= $st && $d <= $totaldays) {


                if (strtotime("$year-$x-$d") < strtotime(date("Y-n-j"))) {

                    echo "<td align='center' class='previous days '><font size = '2' face = 'tahoma'>$d</font></td>";
                } else {

                    if (in_array("$year-$x-$d", $selectedArray)) {

                        echo "<td class='selected days' align='center'><font size = '2' face = 'tahoma'>$d</font></td>";
                    } else {
                        $date = strtotime("$year/$x/$d");
                        $tdDate = 'tdId' . '_' . date("m/d/Y", $date);

                        if (in_array("$d", $blocked)) {
                            echo "<td id=" . $tdDate . " class='normal days " . $d . " ' align='center' style='background-color:red;'><font size = '2' face = 'tahoma'>$d</font></td>";
                        } else if (in_array("$d", $not_avail)) {
                            echo "<td id=" . $tdDate . " class='normal days " . $d . " ' align='center'style='background-color:#F18200;color: black !important;' ><font size = '2' face = 'tahoma'>$d</font></td>";
                        } else if(array_key_exists($d,$_sp)){
                                echo "<td style='padding: 11px 23px;' id=" . $tdDate . " class='normal days " . $d . " ' align='center' ><font size = '2' face = 'tahoma'>$d</font><br><div style='width: 25px;font-size: 1.0em;text-align: right;'>". Mage::app()->getLocale()->currency(Mage::app()->getStore()->
     getCurrentCurrencyCode())->getSymbol()."$_sp[$d]</div></td>";
                            }else{
                            echo "<td id=" . $tdDate . " class='normal days " . $d . " ' align='center' ><font size = '2' face = 'tahoma'>$d</font></td>";
                        }
                    }
                }
                $d++;
            } else {
                echo "<td>&nbsp</td>";
            }

            $ctr++;

            if ($ctr > 7) {
                $ctr = 1;
                echo "</tr>";
            }
        }

        echo '</table>';
        echo '<input type="hidden" value="' . $x . '" id="currentMonth" />';
        echo '<input type="hidden" value="' . $year . '" id="currentYear" />';
    }

    
    public function statusAction() {
        $status = $this->getRequest()->getParam('status');
        $productId = Mage::app()->getRequest()->getParam('productid');
        $status = Mage::getModel('airhotels/airhotels')->status($status, $productId);
        if ($status) {
            echo "Available";
        } else {
            echo "NotAvailable";
        }
    }

    public function albumupdateAction() {
        $post = $this->getRequest()->getPost();
        $entityId = $this->getRequest()->getParam('entity_id');
        $imageCollection = $this->getRequest()->getParam('imageCollection');
        if ($this->getRequest()->getParam('remove') != "0") {
           
            for ($i = 0; $i < count($imageCollection); $i++) {
                if ($imageCollection[$i]) {
                    Mage::getModel('airhotels/airhotels')->removeImage($imageCollection[$i], $entityId);
                }
            }
            
        }
        $this->loadLayout();

        Mage::getModel('airhotels/airhotels')->albumupdate($post);
        $this->renderLayout();
        return $this->_redirectUrl(Mage::helper('airhotels')->getshowlisturl());
    }

    public function reviewAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function reviewstatusAction() {
        $status = $this->getRequest()->getParam('status');
        $reviewid = Mage::app()->getRequest()->getParam('reviewid');
        $status = Mage::getModel('airhotels/airhotels')->review($status, $reviewid);
        if ($status == "2") {
            echo "Available";
        } else {
            echo "NotAvailable";
        }
    }

    public function reviewPageAction() {
        $page = $this->getRequest()->getParam('page');
        $productId = $this->getRequest()->getParam('product');

        $reviews = Mage::getModel('review/review')->getResourceCollection();
        $reviews->addStoreFilter(Mage::app()->getStore()->getId())
                ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                ->addEntityFilter('product', $productId)
                ->setDateOrder()
                ->addRateVotes()
                ->setPageSize(4)->setCurPage($page)
                ->load();
        $reviews = $reviews->getData();
        if (count($reviews)) {
            for ($i = 0; $i < count($reviews); $i++) {
                $customerData = Mage::getModel('airhotels/airhotels')->getCustomerPictureById($reviews[$i]["customer_id"]);
                ?>
                <div class="review-product">
                    <ul>
                        <li class="yourlist_img" style="float: left;"><?php if ($customerData[0]["imagename"]): ?>
                                <img
                                    src="<?php echo Mage::getBaseUrl('media') . "catalog/customer/thumbs/" . $customerData[0]["imagename"] ?>"
                                    style="width: 63px !important; height: 53px !important" alt=""> <?php else: ?>
                                <img
                                    src="<?php echo Mage::getBaseUrl('skin') . "frontend/default/airhotel/images/" ?>"
                                    style="width: 63px !important; height: 53px !important" alt=""> <?php endif; ?>
                        </li>
                        <li
                            style="float: left; width: 588px; padding-left: 10px; border-radius: 5px; -moz-border-radius: 5px; -webkit-border-radius: 5px;">
                            <div class="review-content "><?php echo '<span style="font-weight:bold;font-size:14px;">"</span>' . nl2br($reviews[$i]["detail"]) . '<span style="font-weight:bold;font-size:14px;">"</span>'; ?>
                                <div><?php echo '- ' . $reviews[$i]["nickname"] . ", " . date("dS, F Y", strtotime($reviews[$i]["created_at"])); ?></div>
                            </div>
                        </li>
                    </ul>
                </div>
                <?php
            }
            //getting total count
            $reviewsTotal = Mage::getModel('review/review')->getResourceCollection();
            $reviewsTotal->addStoreFilter(Mage::app()->getStore()->getId())
                    ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                    ->addEntityFilter('product', $productId)
                    ->setDateOrder()
                    ->addRateVotes()
                    ->load();
            $totalRecords = count($reviewsTotal);
            if ($page > 1):
                ?>
                <a href="javascript:void(0);" class='paginationClass'
                   onclick="getPagination('1','<?php echo $productId ?>')"><?php echo $this->__('First'); ?></a>
                <?php
            endif;
            for ($i = 1; $i <= ceil($totalRecords / 4); $i++) {
                if ($i == $page)
                    echo "<a class='paginationClass'  href='javascript:void(0);'>" . $i . "</a>";
                else
                    echo "<a class='paginationClass' href='javascript:void(0);' onclick='getPagination(\"$i\",\"$productId\")' >" . $i . "</a>";
            }
            if (ceil($totalRecords / 4) > $page):
                ?>
                <a href="javascript:void(0);" class="paginationClass"
                   onclick="getPagination('<?php echo ceil($totalRecords / 4); ?>','<?php echo $productId ?>')"><?php echo $this->__('Last'); ?></a>
                   <?php
               endif;
           } else {
               echo $this->__('There are no reviews yet for this product. Be the first to write a review');
           }
       }

       public function contactAction() {
           $this->loadLayout();
           $this->_initLayoutMessages('catalog/session');
           if (!Mage::getSingleton('customer/session')->isLoggedIn()) {  // if not logged in
               echo $this->__('You are not currently logged in');
           } else {
               $this->loadLayout();
               $this->renderLayout();
           }
       }

       public function saveinboxAction() {
           if (!Mage::getSingleton('customer/session')->isLoggedIn()) {  // if not logged in
               $this->_redirectUrl(Mage::helper('customer')->getLoginUrl());
           } else {
               $cid = $this->getRequest()->getParam('cid');
               $pid = $this->getRequest()->getParam('pid');
                      
                
               $from = date("Y-m-d", strtotime($this->getRequest()->getParam('from')));
               $to = date("Y-m-d", strtotime($this->getRequest()->getParam('to')));
               $no_of_guests = $this->getRequest()->getParam('number_of_guests');
               $mailSubject = Mage::helper('airhotels')->phpSlashes($this->getRequest()->getParam('mailSubject'));
               $hostCall = $this->getRequest()->getParam('hostCall');
               //$guest_preferences = $this->getRequest()->getParam('config');
               $guest_preferences=$this->getRequest()->getParam('guest_preferences');
               $mobileNo=$this->getRequest()->getParam('mobileNo');
               $data = array($cid, $pid, $from, $to, $no_of_guests, $mailSubject, $hostCall, $guest_preferences, $mobileNo);
               $model = Mage::getModel('catalog/product');
               $_product = $model->load($pid);

               if (Mage::getModel('airhotels/airhotels')->saveInbox($data)) {
                   Mage::getSingleton('core/session')->addSuccess($this->__('Mail sent successfully'));
               } else {
                   Mage::getSingleton('core/session')->addSuccess($this->__('Mail sending failed'));
               }
               return $this->_redirectUrl(Mage::getBaseUrl() . $_product->getUrlPath());
           }
       }

       public function inboxAction() {
           if (!Mage::getSingleton('customer/session')->isLoggedIn()) {  // if not logged in
               $this->_redirectUrl(Mage::helper('customer')->getLoginUrl());
           } else {
               $messageid = $this->getRequest()->getParam('messageid');

               if ($messageid) {
                   if (Mage::getModel('airhotels/airhotels')->delelteMessage($messageid, "in")) {
                       Mage::getSingleton('core/session')->addSuccess($this->__('Deleted successfully'));
                   } else {
                       Mage::getSingleton('core/session')->addSuccess($this->__('Deletion failed. Try again'));
                   }
               }
               $this->loadLayout();
               $this->renderLayout();
           }
       }

       public function senditemAction() {
           if (!Mage::getSingleton('customer/session')->isLoggedIn()) {  // if not logged in
               $this->_redirectUrl(Mage::helper('customer')->getLoginUrl());
           } else {
               $messageid = $this->getRequest()->getParam('messageid');
               if ($messageid) {
                   if (Mage::getModel('airhotels/airhotels')->delelteMessage($messageid, "out")) {
                       Mage::getSingleton('core/session')->addSuccess($this->__('Deleted successfully'));
                   } else {
                       Mage::getSingleton('core/session')->addSuccess($this->__('Deletion failed. Try again'));
                   }
               }
               $this->loadLayout();
               $this->renderLayout();
           }
       }

       public function showmessageAction() {
           if (!Mage::getSingleton('customer/session')->isLoggedIn()) {  // if not logged in
               $this->_redirectUrl(Mage::helper('customer')->getLoginUrl());
           } else {
               $mess_Id = $this->getRequest()->getParam('id');
               Mage::getSingleton('core/session')->setmId($mess_Id);
               $this->loadLayout();
               $this->renderLayout();
           }
       }

       public function uploadphotoAction() {
           if (!Mage::getSingleton('customer/session')->isLoggedIn()) {  // if not logged in
               $this->_redirectUrl(Mage::helper('customer')->getLoginUrl());
           } else {
               if (isset($_FILES['profilePhoto']['name'])) {
                   Mage::getModel('airhotels/airhotels')->updateProfilePicture();
                   $url = Mage::getBaseUrl() . "airhotels/property/uploadphoto";
                   $this->_redirectUrl($url);
               }
               $this->loadLayout();
               $this->renderLayout();
           }
       }

       public function replyAction() {
           if (!Mage::getSingleton('customer/session')->isLoggedIn()) {  // if not logged in
               $this->_redirectUrl(Mage::helper('customer')->getLoginUrl());
           } else {
               $messageid = $this->getRequest()->getParam('message_id');
               //Mage::getSingleton('core/session')->getmId();
               //$customerid = $this->getRequest()->getParam('customer_id');
               $customer = Mage::getSingleton('customer/session')->getCustomer();
               $customerid = $customer->getId();
               $messageBody = Mage::helper('airhotels')->phpSlashes($this->getRequest()->getParam('message'));
               //$message = mysql_real_escape_string($this->getRequest()->getParam('message'));
                $message = $messageBody;
               if (Mage::getModel('airhotels/airhotels')->replyMail($messageid, $customerid, $message)) {
                   Mage::getSingleton('core/session')->addSuccess($this->__('Mail sent successfully'));
               } else {
                   Mage::getSingleton('core/session')->addError($this->__('Mail sent failed'));
               }
           }
           $url = Mage::getBaseUrl() . "airhotels/property/inbox/";
           Mage::app()->getFrontController()->getResponse()->setRedirect($url);
       }

       public function advsearchAction() { 
           $this->loadLayout();
           $this->renderLayout();
       }
	   
	   public function ajaxmapAction() {
           $this->loadLayout();
           $this->renderLayout();
       }

       public function searchresultAction() { 
           $this->loadLayout();
           $this->renderLayout();
       }
     
   /**
    * Blocking calendar fucntionality start 
    */
   public function blockcalendarAction() {
        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {  // if not logged in
            $this->_redirectUrl(Mage::helper('customer')->getLoginUrl());
        }
            $this->loadLayout();
            $this->renderLayout();
            
       
    }
    /**
     * using this we can insert the deatils blocked dates
     * 
     * @return calendarviewAction();
     */
    
    public function blockdateAction() {

        $fromDate = date("Y-m-d", strtotime($this->getRequest()->getPost('check_in')));
        $toDate = date("Y-m-d", strtotime($this->getRequest()->getPost('check_out')));
        $bookAvail = $this->getRequest()->getPost('book_avail');
        if ($bookAvail == '') {
            $bookAvail = 0;
        }
        $productId = $this->getRequest()->getPost('productid');
        $pricePer = trim($this->getRequest()->getPost('price_per'));
        if ($pricePer == '') {
            //$pricePer = 0;
        }
        $mY = explode("__", $this->getRequest()->getPost('date'));
        $month = $mY[0];
        $year = $mY[1];
        if ($fromDate <= $toDate) {
            $dateValue [] = date("d", strtotime($fromDate));
            $date1 = strtotime($fromDate);
            $date2 = strtotime($toDate);
            while ($date1 != $date2) {
                $date1 = mktime(0, 0, 0, date("m", $date1), date("d", $date1) + 1, date("Y", $date1));
                $dateValue[] = date("d", $date1);
            }
        }
        $fDate = implode(",", $dateValue);
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');
        $write = $resource->getConnection('core_write');
        $tPrefix = (string) Mage::getConfig()->getTablePrefix();
        $blockCalendartable = $tPrefix . 'airhotels_calendar';
        
        // new formate for price
            for($j=0 ; $j<count($dateValue); $j++){
                 $this->datePriceUpdate($productId,$month,$year,$dateValue[$j]);
            }
            if($pricePer !=''){
                $insert = "($productId,$bookAvail,$month,$year,'$fDate',$pricePer,now())";
                $write->query("INSERT INTO $blockCalendartable (product_id,book_avail,month,year,blockfrom,price,created) VALUES $insert");
            }
            //$write->query("Delete from $blockCalendartable WHERE product_id = $productId AND month = '" . $month . "' AND year = '" . $year . "' AND blockfrom = ''");
        //end formate for price
        
        $this->calendarviewAction();
        die();
    }

    // new date price update fucntion
    public function datePriceUpdate($productId,$month,$year,$dateValue){
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');
        $write = $resource->getConnection('core_write');
        $tPrefix = (string) Mage::getConfig()->getTablePrefix();
        $blockCalendartable = $tPrefix . 'airhotels_calendar';
        $select = "SELECT blockfrom from $blockCalendartable
                    WHERE product_id = '$productId'AND month = '" . $month . "' AND year = '" . $year . "'AND blockfrom LIKE '%".$dateValue."%'";
        $result = $read->fetchRow($select);
        if(count($result)>0){
            $dateArr=array();
            $dateArr[]=$dateValue;
            $splitdata = explode(',', $result['blockfrom']);
            $dataArrValue = array_diff($splitdata, $dateArr);
            $implodedArray = implode(",", $dataArrValue);
            $write->query("UPDATE $blockCalendartable SET blockfrom ='$implodedArray' WHERE product_id = $productId AND month = '" . $month . "' AND year = '" . $year . "' AND blockfrom LIKE '%".$dateValue."%'");
        }
    }

    
    public function getDaysAction($count, $value) {
        for ($j = 0; $j < $count; $j++) {
            $availDay[] = $value[$j][1];
        }
        $availDays = explode(",", implode(",", $availDay));
        return $availDays;
    }
       
    /**
     * Add date : 16th August 2012
     * 
     * To load layout for showing the popular property details
     * 
     */
   
    public function popularAction(){
        
          $this->loadLayout();
           $this->renderLayout();
        
    }
      /**
     * Add date : 18th August 2012
     * 
     * To load layout for showing Wish list page
     * 
     */
   
    public function wishlistAction(){
         if (!Mage::getSingleton('customer/session')->isLoggedIn()) {  // if not logged in
               $this->_redirectUrl(Mage::helper('customer')->getLoginUrl());
           }  
          $this->loadLayout();
          $customer = Mage::getSingleton('customer/session')->getCustomer();
          $custName = $customer->getName();
          $this->getLayout()->getBlock('head')->setTitle($this->__('My Wish List').'-'.$custName);
           $this->renderLayout();
        
    }
    // Calender price
    public function getSpecialPriceDaysAction($count, $value) {
        for ($j = 0; $j < $count; $j++) {
            $availDay[$j] = $value[$j][1];
            $avail[$value[$j][1]] = $value[$j][3];
        }
    
            return $avail;
 }
 
 public function mapshowAction()
	{
	
	$this->loadLayout();
           $this->renderLayout();
	}

 }
   