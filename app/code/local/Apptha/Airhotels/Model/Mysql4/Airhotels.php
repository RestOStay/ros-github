<?php
/**
 * @ Author     : Apptha team
 * @package     : Apptha_Airhotels
 * @copyright   : Copyright (c) 2011 (www.apptha.com)
 * @license     : http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class Apptha_Airhotels_Model_Mysql4_Airhotels extends Mage_Core_Model_Mysql4_Abstract
 {

    public function _construct() {
        // Note that the airhotels_id refers to the key field in your database table.
        $this->_init('airhotels/airhotels', 'id');
    }

    
}