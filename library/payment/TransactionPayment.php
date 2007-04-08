<?php

include_once('PaymentException.php');

abstract class TransactionPayment
{		
	protected $transactionDetails;
	
	protected $isTestTransaction = false;
	
	public function __construct(TransactionDetails $transactionDetails)
	{
		$this->transactionDetails = $transactionDetails;
	}
	
	public function setAsTestTransaction($test = true)
	{
		$this->isTestTransaction = true;
	}
	
	/**
	 *	Determine if the payment method supports crediting a refund payment back to customer
	 */
	public abstract function isCreditable();
}

abstract class IPNPaymentMethod
{
	
}

abstract class OfflinePaymentMethod
{
	public function isCreditable()
	{
		return false;
	}		
}

?>