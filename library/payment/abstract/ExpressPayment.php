<?php

include_once(dirname(__file__) . '/../TransactionPayment.php');

abstract class ExpressPayment extends TransactionPayment
{
	abstract public function redirect()
    
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