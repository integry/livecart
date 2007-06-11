<?php

include_once('PaymentException.php');
include_once('TransactionResult.php');
include_once('TransactionError.php');
include_once('TransactionDetails.php');

abstract class TransactionPayment
{		
	protected $details;
	
	protected $isTestTransaction = false;
	
	private $config = array();
	
	public function __construct(TransactionDetails $transactionDetails)
	{
		$this->details = $transactionDetails;
	}
	
	public function setAsTestTransaction($test = true)
	{
		$this->isTestTransaction = true;
	}
	
	public function setConfigValue($key, $value)
	{
		$this->config[$key] = $value;
	}
	
	public function getConfigValue($key)
	{
		if (isset($this->config[$key]))
		{
			return $this->config[$key];
		}
	}
	
	/**
	 *	Determine if the payment method supports crediting a refund payment back to customer
	 */
	public abstract static function isCreditable();
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