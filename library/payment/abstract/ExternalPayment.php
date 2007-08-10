<?php

include_once(dirname(__file__) . '/../TransactionPayment.php');

abstract class ExternalPayment extends TransactionPayment
{
    /**
	 *	Return payment page URL
	 */
	abstract public function getUrl();
	
	/**
	 *	Payment confirmation post-back
	 */
	abstract public function notify($requestArray);
}

?>