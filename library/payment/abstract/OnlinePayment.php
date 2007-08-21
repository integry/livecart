<?php

include_once(dirname(__file__) . '/../TransactionPayment.php');

abstract class OnlinePayment extends TransactionPayment
{
	/**
	 *	Determines if multiple capture transactions are supported for one authorize transaction
	 */
	abstract public function isMultiCapture();

	/**
	 *	Determines if captured transactions can be voided
	 */
	abstract public function isCapturedVoidable();
}

?>