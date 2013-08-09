<?php
/**
 * Facebook Connect session model
 *
 * @category   Inchoo
 * @package    Inchoo_Facebook
 * @author     Ivan Weiler, Inchoo <web@inchoo.net>
 * @copyright  Copyright (c) 2010 Inchoo d.o.o. (http://inchoo.net)
 * @license    http://opensource.org/licenses/gpl-license.php  GNU General Public License (GPL)
 */

/**
 * Modified facebook connect OAuth2.0
 * @author		Sathish kumar(sathishkumar@contus.in)
 * @company		Contus Support Interactive
 * @date		01/09/2011
 */

class Inchoo_Facebook_Model_Session extends Varien_Object
{
	public function __construct()
	{

		$this->setConnected(false);

		if($this->getCookie()){
			$data = array();
			parse_str(trim($this->getCookie(),''), $data);
			//$this->setData($data); //session_key only?
			$data = $this->parse_signed_request($this->getCookie(), Mage::getSingleton('facebook/config')->getSecret());

			if(isset($data['code'])){
				$this->setSessionKey($data['code']);
				$uid = $data['user_id'];
				$this->setUid($uid);
				$this->setConnected(true);
			}
		}
	}

	protected function parse_signed_request($signed_request, $secret) {
		list($encoded_sig, $payload) = explode('.', $signed_request, 2);

		// decode the data
		$sig = $this->base64_url_decode($encoded_sig);
		$data = json_decode($this->base64_url_decode($payload), true);

		if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
			error_log('Unknown algorithm. Expected HMAC-SHA256');
			return null;
		}

		// check sig
		$expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
		if ($sig !== $expected_sig) {
			error_log('Bad Signed JSON signature!');
			return null;
		}

		return $data;
	}

	protected function base64_url_decode($input) {
		return base64_decode(strtr($input, '-_', '+/'));
	}


	public function isConnected($refresh=false)
	{
		if(!$this->validate()) {
    		return false;
    	}
		return true;
	}

	public function validate()
	{
		$params = $this->getData();
		unset($params['sig']);

		return (true);
	}

	public function checkConnection()
	{
		//Users.getLoggedInUser, Users.isAppUser
		try{
			//$isAppUser = $this->getClient()->users->isAppUser(array('session_key' => $this->getSessionKey()));
			//if(!$isAppUser) $this->setConnected(false);
			$this->setConnected(true);
			//prevents UID from being faked && checks session
			//$uid = $this->getClient()->users->getLoggedInUser();
			$data = $this->parse_signed_request($this->getCookie(), Mage::getSingleton('facebook/config')->getSecret());
			$uid = $data['user_id'];
			$this->setUid($uid);

		}catch(Mage_Core_Exception $e){
			$this->setConnected(false);
		}
	}

	public function getPermissions($refresh=false)
	{
		if($this->hasData('permissions') && !$refresh)
		{
			return $this->getData('permissions');
		}

		$validPermissions = $this->getClient()->getValidPermissions();

		$batchQueue = array();
		foreach($validPermissions as $permission) {
			$batchQueue[] = array('method' => 'users.hasAppPermission', 'ext_perm' => $permission, 'uid' => $this->getUid());
		}

		$hasPermissions = $this->getClient()->batch($batchQueue);

		$this->setPermissions(array_combine($validPermissions,$hasPermissions));
	}

	public function hasPermission($permission,$refresh=false)
	{
		$permissions = $this->getPermissions($refresh);
		return $permissions[$permission];
	}

	public function getCookie()
	{
		return Mage::app()->getRequest()->getCookie('fbsr_'.Mage::getSingleton('facebook/config')->getApiKey(), false);
	}

	public function getClient()
	{
		if(!$this->hasData('client')) {
			$this->setClient(
			Mage::getModel('facebook/client',array(
			Mage::getSingleton('facebook/config')->getApiKey(),
			Mage::getSingleton('facebook/config')->getSecret(),
			$this->getSessionKey()
			))
			);
		}

		return $this->getData('client');
	}

}