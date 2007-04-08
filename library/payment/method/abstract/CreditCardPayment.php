<?php

abstract class CreditCardPayment
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
	
	public function setCardData($cardNumber, $expiryMonth, $expiryYear, $cardCode = null)
	{
		$this->cardNumber = $cardNumber;
		$this->expiryMonth = $expiryMonth;
		$this->expiryYear = $expiryYear;
		$this->cardCode = $cardCode;				
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