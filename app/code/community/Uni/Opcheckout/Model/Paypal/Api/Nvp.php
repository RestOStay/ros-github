<?php

class Uni_Opcheckout_Model_Paypal_Api_Nvp extends Mage_Paypal_Model_Api_Nvp
{
    protected function _exportAddressses($data)
    {
        $version = Mage::getVersionInfo();
        if($version['major']>=1 && $version['minor']>=7){
            parent::_exportAddressses($data);
        }else{
            $address = new Varien_Object();
            Varien_Object_Mapper::accumulateByMap($data, $address, $this->_billingAddressMap);
            $address->setExportedKeys(array_values($this->_billingAddressMap));
            $this->_applyStreetAndRegionWorkarounds($address);
            $this->setExportedBillingAddress($address);
            // assume there is shipping address if there is at least one field specific to shipping
            if (isset($data['SHIPTONAME'])) {
                $shippingAddress = clone $address;
                Varien_Object_Mapper::accumulateByMap($data, $shippingAddress, $this->_shippingAddressMap);
                $this->_applyStreetAndRegionWorkarounds($shippingAddress);
                // PayPal doesn't provide detailed shipping name fields, so the name will be overwritten
                $firstName = $data['SHIPTONAME'];
                $lastName = null;
                if (isset($data['FIRSTNAME']) && $data['LASTNAME']) {
                    $firstName = $data['FIRSTNAME'];
                    $lastName = $data['LASTNAME'];
                }            
                $shippingAddress->addData(array(
                    'prefix'     => null,
                    'firstname'  => $firstName,
                    'middlename' => null,
                    'lastname'   => $lastName,
                    'suffix'     => null,
                ));
                $this->setExportedShippingAddress($shippingAddress);
            }
        }
    }
}
