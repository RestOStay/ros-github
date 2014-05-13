<?php

$installer = $this;

$installer->startSetup();
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->run("
        
-- DROP TABLE IF EXISTS {$this->getTable('airhotels_calendar')};
CREATE TABLE IF NOT EXISTS `airhotels_calendar` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `product_id` int(10) NOT NULL,
  `book_avail` int(10) NOT NULL,
  `month` int(10) NOT NULL,
  `year` int(10) NOT NULL,
  `blockfrom` varchar(500) NOT NULL,
  `price` decimal(10,0) NOT NULL,
  `created` date NOT NULL,
  `updated` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

");

$installer->endSetup();