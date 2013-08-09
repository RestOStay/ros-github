<?php
/**
 * @ Author      : Apptha team
 * @package      : Apptha_Airhotels
 * @copyright    : Copyright (c) 2011 (www.apptha.com)
 * @license      : http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @Modified Date:June 2012
 */

class Apptha_Airhotels_Model_Sales_Order_Total_Invoice_Fee extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
	/**
	 * Collect invoice grandtotal
	 *
	 * @param Mage_Sales_Model_Quote_Address $address
	 * @return Apptha_Airhotels_Model_Sales_Order_Total_Invoice_Fee
	 */

	public function collect(Mage_Sales_Model_Order_Invoice $invoice)
	{
		$order = $invoice->getOrder();
               
		$feeAmountLeft = $order->getFeeAmount() - $order->getFeeAmountInvoiced();
		$baseFeeAmountLeft = $order->getBaseFeeAmount() - $order->getBaseFeeAmountInvoiced();
		if (abs($baseFeeAmountLeft) < $invoice->getBaseGrandTotal()) {
			$invoice->setGrandTotal($invoice->getGrandTotal() + $feeAmountLeft);
			$invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseFeeAmountLeft);
		} else {
			$feeAmountLeft = $invoice->getGrandTotal() * -1;
			$baseFeeAmountLeft = $invoice->getBaseGrandTotal() * -1;

			$invoice->setGrandTotal(0);
			$invoice->setBaseGrandTotal(0);
		}
			
		$invoice->setFeeAmount($feeAmountLeft);
		$invoice->setBaseFeeAmount($baseFeeAmountLeft);
		return $this;
	}
}
