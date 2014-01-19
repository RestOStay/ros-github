<?php
/**
 * @ Author      : Apptha team
 * @package      : Apptha_Airhotels
 * @copyright    : Copyright (c) 2011 (www.apptha.com)
 * @license      : http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @Modified Date:June 2012
 * Shopping cart controller
 */

require_once 'Mage/Checkout/controllers/CartController.php';

class Apptha_Airhotels_Checkout_CartController extends Mage_Checkout_CartController {

	/**
	 * Add product to shopping cart action
	 */



	public function addAction()
	{
			
		$cart = $this->_getCart();
		$params = $this->getRequest()->getParams();
                
		/* start
		 * coding used for restricting user to add more than one 1 item in the cart.
		 *
		 *
		 */
		$session = Mage::getSingleton('checkout/session');
		$productId = "";

		foreach ($session->getQuote()->getAllItems() as $item) {
			$productId = $item->getProductId();
		}
		$cartItems = Mage::helper('checkout/cart')->getCart()->getItemsCount();
		if ($cartItems >= 1) {
			$this->_getSession()->addError($this->__('Maximum one property can be added.'));
			$this->_goBack();
			return;

		}
		/* end */

		try {
			/*Setting cart session values*/
			$fromdate = date("Y-m-d", strtotime(str_replace('@', '/', $params['fromdate'])));
			$todate = date("Y-m-d", strtotime(str_replace('@', '/', $params['todate'])));
			$serviceFee = $params['serviceFee'];
			$accomodate = str_replace('@', '/', $params['accomodate']);
			$prod_id = $params['accomodate'];
			$subtotal_amt = $params['subtotal_amt'];
                        Mage::getSingleton('core/session')->setFromdate($fromdate);
			Mage::getSingleton('core/session')->setTodate($todate);
			Mage::getSingleton('core/session')->setAccomodate($accomodate);
			Mage::getSingleton('core/session')->setProdId($prod_id);
			Mage::getSingleton('core/session')->setSubtotal($subtotal_amt);
			Mage::getSingleton('core/session')->setServiceFee($serviceFee);
			/*End*/
                        if (isset($params['qty'])) {
				$filter = new Zend_Filter_LocalizedToNormalized(
				array('locale' => Mage::app()->getLocale()->getLocaleCode())
				);

				//$params['qty'] = $filter->filter($params['qty']);

                                $start = strtotime($fromdate);
                                $end = strtotime($todate);
                                $daysBetween = ceil(abs($end - $start) / 86400);
                                $params['qty'] = $daysBetween;
			}

			$product = $this->_initProduct();
			$related = $this->getRequest()->getParam('related_product');

			/**
			 * Check product availability
			 */
			if (!$product) {
				$this->_goBack();
				return;
			}
			$cart->addProduct($product, $params);
			if (!empty($related)) {
				$cart->addProductsByIds(explode(',', $related));
			}

			$cart->save();

			$this->_getSession()->setCartWasUpdated(true);

			/**
			 * @todo remove wishlist observer processAddToCart
			 */
			Mage::dispatchEvent('checkout_cart_add_product_complete',
			array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
			);

			if (!$this->_getSession()->getNoCartRedirect(true)) {
				if (!$cart->getQuote()->getHasError()){
					$message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->htmlEscape($product->getName()));
					$this->_getSession()->addSuccess($message);
				}
				$this->_goBack();
			}
		} catch (Mage_Core_Exception $e) {
			if ($this->_getSession()->getUseNotice(true)) {
				$this->_getSession()->addNotice($e->getMessage());
			} else {
				$messages = array_unique(explode("\n", $e->getMessage()));
				foreach ($messages as $message) {
					$this->_getSession()->addError($message);
				}
			}

			$url = $this->_getSession()->getRedirectUrl(true);
			if ($url) {
				$this->getResponse()->setRedirect($url);
			} else {
				$this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
			}
		} catch (Exception $e) {
			$this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
			Mage::logException($e);
			$this->_goBack();
		}
	}



}
