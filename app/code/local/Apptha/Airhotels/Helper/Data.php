<?php
/**
 * @ Author      : Apptha team
 * @package      : Apptha_Airhotels
 * @copyright    : Copyright (c) 2011 (www.apptha.com)
 * @license      : http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @Modified Date:June 2012
 */
class Apptha_Airhotels_Helper_Data extends Mage_Core_Helper_Abstract
{
    const NO_OF_ACCOMMODATES = 90;
    const NO_OF_ROOMS = 90;

    public function getNoOfAccomodates() {
        return self:: NO_OF_ACCOMMODATES;
    }

    public function getNoOfRooms() {
        return self:: NO_OF_ACCOMMODATES;
    }

    /**
	 * Retrieve property form post url
	 *
	 * @return string
	 */
	public function getformurl()
	{
		return $this->_getUrl('airhotels/property/form');
	}
        /**
	 * Retrieve calendar
	 *
	 * @return string
	 */
        public function getblockurl(){
            
          return $this->_getUrl('airhotels/property/blockdate');
        }
        
	/**
	 * List property using show post url
	 *
	 * @return string
	 */
	public function getyourlisturl()
	{
		return $this->_getUrl('airhotels/property/show') ;

	}
	/**
	 * List property regarding to current,upcoming and previous trip using yourtrip post url
	 *
	 * @return string
	 */

	public function getyourtripurl()
	{
		return $this->_getUrl('airhotels/property/yourtrip') ;

	}
	/**
	 * Retrieve property edit post url
	 *
	 * @return string
	 */
	public function getediturl()
	{
		return $this->_getUrl('airhotels/property/edit') ;

	}
	/**
	 * Upload property images gallery uisng image post url
	 *
	 * @return string
	 */

	public function getimageurl()
	{
		return $this->_getUrl('airhotels/property/image');

	}
	/**
	 * After updating property album it redirects to property list page
	 *
	 * @return string
	 */

	public function getshowlisturl()
	{
		return $this->_getUrl('airhotels/property/show') ;

	}
    /**
	 * After updating property album it redirects to property list page
	 *
	 * @return string
	 */

	public function getcalendarurl()
	{
		return $this->_getUrl('airhotels/property/calender') ;

	}
	
	
	/**
     * Retrieve attribute id for accomodates
     *
     * @return (int)accomodatesId
	 */
	public  function getaccomodates()
	{
		return Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'accomodates');


	}
	/**
     * Retrieve attribute id for amenity
     *
     * @return (int)amenityId
	 */
	public  function getamenity()
	{
		return Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'amenity');


	}
	/**
     * Retrieve attribute id for description
     *
     * @return (int)descriptionId
	 */
	public  function getdescription()
	{
		return Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'description');

	}
	/**
     * Retrieve attribute id for short_description
     *
     * @return (int)short_descriptionId
	 */
	public  function getshortdescription()
	{
		return Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'short_description');

	}
	/**
     * Retrieve attribute id for hostemail
     *
     * @return (int)hostemailId
	 */
	public  function gethostemail()
	{
		return Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'hostemail');
			
	}
	/**
     * Retrieve attribute id for propertytype
     *
     * @return (int)propertytypeId
	 */
	public  function getpropertytype()
	{
		return Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'propertytype');
			

	}
	/**
     * Retrieve attribute id for name
     *
     * @return (int)nameId
	 */
	public  function getname()
	{
	 return Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'name');

	}
	/**
     * Retrieve attribute id for price
     *
     * @return (int)priceId
	 */
	public  function getprice()
	{
		return Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'price');

	}
	/**
     * Retrieve attribute id for propertyadd
     *
     * @return (int)propertyaddId
	 */
	public  function getaddress()
	{
		return Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'propertyadd');
			
	}
	/**
     * Retrieve attribute id for totalrooms
     *
     * @return (int)totalroomsId
	 */
	public  function getroom()
	{
		return Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'totalrooms');
			
	}
	/**
     * Retrieve attribute id for privacy
     *
     * @return (int)privacyId
	 */
	public  function getprivacy()
	{
		return Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'privacy');
			
	}
	/**
     * Retrieve attribute id for image
     *
     * @return (int)imageId
	 */
	public  function getbaseimage()
	{
		return Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'image');
			
	}
	/**
     * Retrieve attribute id for small_image
     *
     * @return (int)small_imageId
	 */
	public  function getsmallimage()
	{
		return Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'small_image');
			
	}
	/**
     * Retrieve attribute id for thumbnail
     *
     * @return (int)thumbnailId
	 */
	public  function getthumbimage()
	{
		return Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'thumbnail');
			
	}
	/**
     * Retrieve attribute id for media_gallery
     *
     * @return (int)media_galleryId
	 */
	public  function getmediagallery()
	{
		return Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'media_gallery');
			
	}
	/**
     * Retrieve attribute id for state
     *
     * @return (int)stateId
	 */
	public  function getstate()
	{
		return Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'state');
			
	}
	/**
     * Retrieve attribute id for city
     *
     * @return (int)cityId
	 */
	public  function getcity()
	{
		return Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'city');
			
	}
	/**
     * Retrieve attribute id for country
     *
     * @return (int)countryId
	 */
	public  function getcountry()
	{
		return Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'country');
			
	}
	/**
     * Retrieve attribute id for cancelpolicy
     *
     * @return (int)cancelpolicyId
	 */
	public  function getcancelpolicy()
	{
		return Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'cancelpolicy');
			
	}
	/**
     * Retrieve attribute id for pets
     *
     * @return (int)petsId
	 */
	public  function getpets()
	{
		return Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'pets');
			
	}
	/**
     * Retrieve attribute id for maplocation
     *
     * @return (int)maplocationId
	 */
	public  function getmap()
	{
		return Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'maplocation');
			
	}
    /**
     * Retrieve attribute id for bedtype
     *
     * @return (int)bedtypeId
	 */
	public  function getbedtype()
	{
		return Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'bedtype');
			
	}
     /**
     * Retrieve attribute id for accomodates
     *
     * @return (int)accomodates
     */
	public  function getaccomodatesType()
	{
		return Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product','accomodates');
			
	}
        /**
     * Retrieve attribute id for totalrooms
     *
     * @return (int)totalrooms
     */
	public  function getrooms()
	{
		return Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product','totalrooms');
			
	}
     /**
      * Add Date : 24th july 2012
      * Host profile page
      *
      * @return string
      *
      */

     public function getprofilepage()
     {
         return $this->_getUrl('airhotels/index/profile');
     }
        
    /**
     * Database connection object insert ans update 
     *
     * @return (object)write
	 */
	public function getWRAdapter()
	{

		return Mage::getSingleton('core/resource')->getConnection('core_write');

	}
	/**
     * Database connection object fetch data 
     *
     * @return (object)read
	 */
	public function getRDAdapter()
	{
		return Mage::getSingleton('core/resource')->getConnection('core_read');

	}
        
        /**
         *Thif unction is used to check the server support the addSlashes / mysql_real_escape_string
         * @param string $string
         * @param string $type
         * @return string $string
         */
        
       public  function phpSlashes($string,$type='add'){
        if ($type == 'add')
        {
            if (get_magic_quotes_gpc())
            {
                return $string;
            }
            else
            {
                if (function_exists('addslashes'))
                {
                    return addslashes($string);
                }
                else
                {
                    return mysql_real_escape_string($string);
                }
            }
        }
        else if ($type == 'strip')
        {
            return stripslashes($string);
        }
        else
        {
            die('error in PHP_slashes (mixed,add | strip)');
        }
    }
    
     /**
      * Add Date : 17th Aug 2012
      * Host profile page
      *
      * @return string
      *
      */

     public function getpopularpage()
     {
         return $this->_getUrl('airhotels/property/popular');
     }
     
     /**
      * Add Date : 18th Aug 2012
      * Host profile page
      *
      * @return string
      *
      */

     public function getwishlistpage()
     {
         return $this->_getUrl('airhotels/property/wishlist');
     }
}
