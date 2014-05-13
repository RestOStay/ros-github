<?php

$installer = $this;

$installer->startSetup();
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->run("
ALTER TABLE  {$this->getTable('airhotels_customer_inbox')} ADD `mobileNo` varchar(20) NOT NULL default '';
");

$installer->endSetup();