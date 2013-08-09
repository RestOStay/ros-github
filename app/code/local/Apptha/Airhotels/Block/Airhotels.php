<?php

/**
 *
 * @ Author     : Apptha team
 * @copyright   : Copyright (c) 2012 (www.apptha.com)
 * @license     : http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class Apptha_Airhotels_Block_Airhotels extends Mage_Core_Block_Template {

    public function _prepareLayout() {
        return parent::_prepareLayout();
    }

    public function getAirhotels() {
        if (!$this->hasData('airhotels')) {
            $this->setData('airhotels', Mage::registry('airhotels'));
        }
        return $this->getData('airhotels');
    }

    public function showratingCode($count=0) {
        for ($x = 1; $x <= $count; $x++) {
            echo "<img style='float:left'  src='" .$this->getSkinUrl('images/red.png'). "' width='16' height='16' />";
        }
        for ($i = $x; $i <= 5; $i++) {
            echo "<img style='float:left'  src='" .$this->getSkinUrl('images/grey.png'). "' width='16' height='16' />";
        }
    }

}