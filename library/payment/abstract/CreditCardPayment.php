<?php

include_once(dirname(__file__) . '/../TransactionPayment.php');

abstract class CreditCardPayment extends TransactionPayment
{
	/**
	 *	Credit card number (without spaces)
	 */
	protected $cardNumber;
	
	/**
	 *	Credit card expiration month
	 */
	protected $expiryMonth;
	
	/**
	 *	Credit card expiration year
	 */
	protected $expiryYear;
	
	/**
	 *	CVV2, CVC2 or CID code
	 */
	protected $cardCode;
	
	/**
	 *	Credit card type (Visa, MasterCard, etc.)
	 */
	protected $cardType;
	
	const TYPE_VISA = 'Visa';
	const TYPE_MC = 'MasterCard';
	const TYPE_AMEX = 'American Express';
	
	public function setCardData($cardNumber, $expiryMonth, $expiryYear, $cardCode = null)
	{
		$this->cardNumber = $cardNumber;
		$this->expiryMonth = $expiryMonth;
		$this->expiryYear = $expiryYear;
		$this->cardCode = $cardCode;				
	}
	
	public function setCardType($type)
	{
		$this->cardType = $type;	
	}
	
	public function getCardNumber()
	{
		return $this->cardNumber;
	}
	
	public function getExpirationMonth()
	{
		return $this->expiryMonth;
	}
	
	public function getExpirationYear()
	{
		return $this->expiryYear;
	}
	
	public function getCardCode()
	{
		return $this->cardCode;
	}
	
	public function getCardType()
	{
		return $this->cardType;
	}
	
	public function toArray()
	{
		$ret = array();
		$ret['type'] = 'CC';		
		return $ret;
	}
	
	/**
	 *	Reserve funds on customers credit card
	 */
	abstract public function authorize();
	
	/**
	 *	Capture reserved funds
	 */
	abstract public function capture();
	
	/**
	 *	Authorize and capture funds within one transaction
	 */
	abstract public function authorizeAndCapture();
	
	/**
	 *	Refund a payment back to customers card
	 */
	abstract public function credit();
}

?>