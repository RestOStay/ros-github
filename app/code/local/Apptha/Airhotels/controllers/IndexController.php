<?php

/**
 * @ Author      : Apptha team
 * @package      : Apptha_Airhotels
 * @copyright    : Copyright (c) 2011 (www.apptha.com)
 * @license      : http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @Modified Date:June 2012
 */
class Apptha_Airhotels_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Restostay'));
        $this->renderLayout();
    }

    /**
     * Function is used to view the product details  in the page
     */
    public function viewAction() {
        $id = Mage::app()->getRequest()->getParam('id');
        $productIds = array();
        $productIds[0] = $id;
        Mage::getSingleton('core/session')->setProductIds($productIds);
        $this->loadLayout();
        if (isset($id)) {
            $productId = $id;
            $model = Mage::getModel('catalog/product'); //getting product model
            $_product = $model->load($productId);
            $this->getLayout()->getBlock('head')->setTitle(htmlspecialchars($_product->getMetaTitle()));
            $this->getLayout()->getBlock('head')->setKeywords(htmlspecialchars($_product->getMetaKeyword()));
            $this->getLayout()->getBlock('head')->setDescription(htmlspecialchars($_product->getMetaDescription()));
        }
        $this->renderLayout();
    }

    public function updatemoreAction() {
        $responseTime = Mage::app()->getRequest()->getParam('responseTime');
        $moreabouthost = addslashes(Mage::app()->getRequest()->getParam('moreabouthost'));
        $result = Mage::getModel('airhotels/property')->updateProfileInformation($responseTime, $moreabouthost);
        if ($result) {
            Mage::getSingleton('core/session')->addSuccess($this->__('Profile update successfully'));
        } else {
            Mage::getSingleton('core/session')->addError($this->__('Failed to update'));
        }
        $url = Mage::getBaseUrl() . "airhotels/property/uploadphoto";
        $this->_redirectUrl($url);
    }

    public function profileAction() {
//        $customerid = Mage::getSinglet$customeridon('core/session')->getProfileId();
        $customerid = Mage::app()->getRequest()->getParam('id');
        $customer = $address = Mage::getModel('customer/address')->load($customerid);
        $cust_name = $customer->getName();
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__($cust_name));
        $this->renderLayout();
    }

     public function addAction() {
        $row = 0;
        $file = "skin/frontend/default/stylish/sample_data/catalog_product.csv";
        if (($handle = fopen($file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {   // echo '<pre>';print_r($data);exit;
                $num = count($data);
                $row++;
                if ($row == 1)
                    continue;
                $product = Mage::getModel('catalog/product');
                $product->setSku($data[0]);
                $product->setAccomodates($data[7]);
                $product->setAmenity($data[8]);
                $product->setBedtype($data[9]);
                $product->setCancelpolicy($data[10]);
                $product->setCity($data[11]);
                $product->setColor($data[12]);
                $product->setCost($data[13]);
                $product->setCountry($data[14]);
                $product->setCountryOfManufacture($data[15]);
                $product->setCreatedAt($data[16]);
                $product->setDescription($data[21]);
                $product->setEnableGooglecheckout($data[22]);
                $product->setGallery($data[23]);
                $product->setGiftMessageAvailable($data[24]);
                $product->setHasOptions($data[25]);
                $product->setHostemail($data[26]);
                $product->setHouserule($data[27]);
                $product->setImage($data[28]);
                $product->setImageLabel($data[29]);
                $product->setMediaGallery($data[31]);
                $product->setMinimalPrice($data[35]);
                $product->setMsrp($data[36]);
                $product->setMsrpDisplayActualPriceType($data[37]);
                $product->setMsrpEnabled($data[38]);
                $product->setName($data[39]);
                $product->setNewsFromDate(strtotime($data[40]));
                $product->setNewsToDate('');
                $product->setOptionsContainer($data[42]);
                $product->setPrice($data[44]);
                $product->setPrivacy($data[45]);
                $product->setPropertyadd($data[46]);
                $product->setPropertytype($data[47]);
                $product->setPropertyWebsite($data[48]);
                $product->setRequiredOptions($data[49]);
                $product->setShortDescription($data[50]);
                $product->setSmallImage($data[51]);
                $product->setSmallImageLabel($data[52]);
                $product->setSpecialFromDate($data[53]);
                $product->setSpecialPrice($data[54]);
                $product->setSpecialToDate($data[55]);
                $product->setStatus($data[56]);
                $product->setTaxClassId($data[57]);
                $product->setThumbnail($data[58]);
                $product->setThumbnailLabel($data[59]);
                $product->setTotalrooms($data[60]);
                $product->setUpdatedAt($data[61]);
                $product->setUrlKey($data[62]);
                $product->setUrlPath($data[63]);
                $product->setUserid($data[64]);
                $product->setVisibility($data[65]);
                $product->setWeight($data[66]);
                $product->setQty($data[67]);
                $product->setMinQty($data[68]);
                $product->setUseConfigMinQty($data[69]);
                $product->setIsQtyDecimal($data[70]);
                $product->setBackorders($data[71]);
                $product->setUseConfigBackorders($data[72]);
                $product->setMinSaleQty($data[73]);
                $product->setUseConfigMinSaleQty($data[74]);
                $product->setMaxSaleQty($data[75]);
                $product->setUseConfigMaxSaleQty($data[76]);
                $product->setIsInStock($data[77]);
                $product->setAttributeSetId(4); // need to look this up
                $product->setCategoryIds(array($categories[$data[0]])); // need to look these up
                $product->setManufacturer('');
                $product->setTypeId('property');

                // assign product to the default website
                $product->setWebsiteIds(array(Mage::app()->getStore(true)->getWebsite()->getId()));
                $stockData = $product->getStockData();
                $stockData['qty'] = $data[67]; //18
                $stockData['is_in_stock'] = $data[77] == "In Stock" ? 1 : 0; //17
                $stockData['manage_stock'] = 0;
                $stockData['use_config_manage_stock'] = 0;
                $product->setStockData(array(
                    'is_in_stock' => 1,
                    'qty' => 100000
                ));
                try {
                    $product->save();
                } catch (Exception $ex) {
                    $this->_redirectUrl(Mage::getBaseUrl());
                    //Handle the error
                }
            }
            fclose($handle);
            $this->_redirectUrl(Mage::getBaseUrl());
        }
    }

}