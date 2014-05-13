<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
ALTER TABLE `airhotels_booking`  ADD `rooms` INT(10) NOT NULL  AFTER `accomodates`;
ALTER TABLE `airhotels_calendar`  ADD `rooms` INT(10) NOT NULL  AFTER `book_avail`;
SQLTEXT;

$installer->run($sql); 
$installer->endSetup();
	 