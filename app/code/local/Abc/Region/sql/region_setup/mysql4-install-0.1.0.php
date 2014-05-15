<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
INSERT INTO `directory_country_region` VALUES
(NULL , "IN", "Andaman and Nicobar","Andaman and Nicobar"),
(NULL , "IN", "Andhra Pradesh","Andhra Pradesh"),
(NULL , "IN", "Arunachal Pradesh","Arunachal Pradesh"),
(NULL , "IN", "Assam","Assam"),
(NULL , "IN", "Bihar","Bihar"),
(NULL , "IN", "Chandigarh","Chandigarh"),
(NULL , "IN", "Chhattisgarh","Chhattisgarh"),
(NULL , "IN", "Dadra and Nagar Haveli","Dadra and Nagar Haveli"),
(NULL , "IN", "Daman and Diu","Daman and Diu"),
(NULL , "IN", "Delhi","Delhi"),
(NULL , "IN", "Goa","Goa"),
(NULL , "IN", "Gujarat","Gujarat"),
(NULL , "IN", "Haryana","Haryana"),
(NULL , "IN", "Himachal Pradesh","Himachal Pradesh"),
(NULL , "IN", "Jammu and Kashmir","Jammu and Kashmir"),
(NULL , "IN", "Jharkhand","Jharkhand"),
(NULL , "IN", "Karnataka","Karnataka"),
(NULL , "IN", "Kerala","Kerala"),
(NULL , "IN", "Lakshadweep","Lakshadweep"),
(NULL , "IN", "Madhya Pradesh","Madhya Pradesh"),
(NULL , "IN", "Maharashtra","Maharashtra"),
(NULL , "IN", "Manipur","Manipur"),
(NULL , "IN", "Meghalaya","Meghalaya"),
(NULL , "IN", "Mizoram","Mizoram"),
(NULL , "IN", "Nagaland","Nagaland"),
(NULL , "IN", "Orissa","Orissa"),
(NULL , "IN", "Pondicherry","Pondicherry"),
(NULL , "IN", "Punjab","Punjab"),
(NULL , "IN", "Rajasthan","Rajasthan"),
(NULL , "IN", "Sikkim","Sikkim"),
(NULL , "IN", "Tamil Nadu","Tamil Nadu"),
(NULL , "IN", "Tripura","Tripura"),
(NULL , "IN", "Uttar Pradesh","Uttar Pradesh"),
(NULL , "IN", "Uttaranchal","Uttaranchal"),
(NULL , "IN", "West Bengal","West Bengal");
  
INSERT INTO `directory_country_region_name` (`locale` ,`region_id` ,`name` ) 
    SELECT 'en_US', tmp.region_id, tmp.default_name FROM `directory_country_region` 
        AS tmp WHERE tmp.country_id='IN';
SQLTEXT;

$installer->run($sql);
//demo 
//Mage::getModel('core/url_rewrite')->setId(null);
//demo 
$installer->endSetup();
	 