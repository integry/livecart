<?php

include_once('PaymentException.php');
include_once('TransactionResult.php');
include_once('TransactionError.php');
include_once('TransactionDetails.php');

/**
 *
 * @package library.payment
 * @author Integry Systems
 */
abstract class TransactionPayment
{
	protected $details;

	protected $isTestTransaction = false;

	protected $application;

	protected $order;

	private $config = array();

	public function __construct(TransactionDetails $transactionDetails = null)
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

	public function getConfigValue($key, $defaultValue = '')
	{
		if (isset($this->config[$key]))
		{
			return $this->config[$key];
		}
		else
		{
			return $defaultValue;
		}
	}

	public function getDetails()
	{
		return $this->details;
	}

	public function setDetails(TransactionDetails $transactionDetails)
	{
		$this->details = $transactionDetails;
	}

	public function setApplication(LiveCart $application)
	{
		$this->application = $application;
	}

	public function setOrder(CustomerOrder $order)
	{
		$this->order = $order;
		$this->order->setPaymentMethod(get_class($this));
	}

	/**
	 *	Determine if the payment method supports crediting a refund payment back to customer
	 */
	public abstract function isVoidable();

	public abstract function void();

	/**
	 *	Return a valid currency code if the supplied currency is not supported by this payment method
	 */
	public abstract function getValidCurrency($currentCurrencyCode);
}

?>