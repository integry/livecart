<?php

class PaymentController extends FrontendController
{
	public function payCreditCard()
	{
		ClassLoader::import('library.payment.TransactionDetails');
		$transaction = new TransactionDetails();	
	}
}

?>