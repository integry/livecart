<?php

include_once('unittest/UTStandalone.php');

include_once('PaymentTest.php');
include_once('../method/Paypal.php');

class TestPaypal extends PaymentTest
{
	function testAuthorization()
	{
		
		$payment = new Paypal($this->details);
		
		$payment->authorizeAndCapture();
		
		//return $details;
	}	
}

?>