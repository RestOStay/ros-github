<?php

$installer = $this;

$installer->startSetup();
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->run("
ALTER TABLE  {$this->getTable('airhotels_booking')} ADD `product_name` varchar(500) NOT NULL default '';
ALTER TABLE  `".$this->getTable('airhotels_booking')."` ADD `customer_email` varchar(250) NOT NULL default '';
ALTER TABLE  `".$this->getTable('airhotels_booking')."` ADD `base_currency_code` varchar(25) NOT NULL default '';
ALTER TABLE  `".$this->getTable('airhotels_booking')."` ADD `order_currency_code` varchar(25) NOT NULL default '';
ALTER TABLE  `".$this->getTable('airhotels_booking')."` ADD `message` text NOT NULL default '';
ALTER TABLE `".$this->getTable('airhotels_customer_inbox')."` ADD `isdelete` tinyint(10) NOT NULL;
ALTER TABLE  `".$this->getTable('sales/quote_address')."` ADD  `fee_amount` DECIMAL( 10, 2 ) NOT NULL;
ALTER TABLE  `".$this->getTable('sales/quote_address')."` ADD  `base_fee_amount` DECIMAL( 10, 2 ) NOT NULL;
ALTER TABLE  `".$this->getTable('sales/order')."` ADD  `fee_amount` DECIMAL( 10, 2 ) NOT NULL;
ALTER TABLE  `".$this->getTable('sales/order')."` ADD  `base_fee_amount` DECIMAL( 10, 2 ) NOT NULL;

");

$installer->endSetup();