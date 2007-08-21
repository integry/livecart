<?php

include_once(dirname(__file__) . '/OnlinePayment.php');

abstract class ExpressPayment extends OnlinePayment
{
	abstract public function getInitUrl($returnUrl, $cancelUrl, $sale = true);
    
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
}

?>