<?php
/**
 * @ Author      : Apptha team
 * @package      : Apptha_Airhotels
 * @copyright    : Copyright (c) 2011 (www.apptha.com)
 * @license      : http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @Modified Date:June 2012
 */
class Apptha_Airhotels_Model_Sales_Order_Total_Creditmemo_Fee extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
	/**
	 * Collect creditmemo grandtotal
	 *
	 * @param Mage_Sales_Model_Quote_Address $address
	 * @return Apptha_Airhotels_Model_Sales_Order_Total_Creditmemo_Fee
	 */
	public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
	{
		$order = $creditmemo->getOrder();
		$feeAmountLeft = $order->getFeeAmountInvoiced() - $order->getFeeAmountRefunded();
		$basefeeAmountLeft = $order->getBaseFeeAmountInvoiced() - $order->getBaseFeeAmountRefunded();
		if ($basefeeAmountLeft > 0) {
			$creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $feeAmountLeft);
			$creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $basefeeAmountLeft);
			$creditmemo->setFeeAmount($feeAmountLeft);
			$creditmemo->setBaseFeeAmount($basefeeAmountLeft);
		}
		return $this;
	}
}
