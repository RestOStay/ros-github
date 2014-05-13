<?php

$installer = $this;

$installer->startSetup();
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->run("
ALTER TABLE `airhotels_booking`  ADD `rooms` INT(10) NOT NULL  AFTER `accomodates`;
ALTER TABLE `airhotels_calendar`  ADD `rooms` INT(10) NOT NULL  AFTER `book_avail`;

");

$installer->endSetup();
