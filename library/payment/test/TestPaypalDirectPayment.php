<?php

include_once('unittest/UTStandalone.php');

include_once('PaymentTest.php');
include_once('../method/paypaldirectpayment/PaypalDirectPayment.php');

class TestPaypalDirectPayment extends PaymentTest
{
	function testInvalidCard()
	{
		$payment = new PaypalDirectPayment($this->details);
		$payment->setCardData('5522219712684510', '12', '2007', '000');
		$payment->setCardType('Visa');
		
		$result = $payment->authorizeAndCapture();
		
		$this->assertTrue($result instanceof TransactionError);		
	}

	function testAuthorizeAndCapture()
	{		
		$payment = new PaypalDirectPayment($this->details);
		$payment->setCardData('4522219712684510', '12', '2007', '000');
		$payment->setCardType('Visa');
		
		$result = $payment->authorizeAndCapture();
		
		$this->assertTrue($result instanceof TransactionResult);
	}	
	
	function testAuthorizationWithSeparateCapture()
	{		
		$payment = new PaypalDirectPayment($this->details);
		$payment->setCardData('4522219712684510', '12', '2007', '000');
		$payment->setCardType('Visa');
		
		$result = $payment->authorize();
		$this->assertTrue($result instanceof TransactionResult);
		
		$this->details->gatewayTransactionID->set($result->gatewayTransactionID->get());		
		
		$capture = new PaypalDirectPayment($this->details);
		$result = $capture->capture();
		
		$this->assertTrue($result instanceof TransactionResult);
	}	
	
	function testAuthorizationWithHugeCaptureAmount()
	{		
		$payment = new PaypalDirectPayment($this->details);
		$payment->setCardData('4522219712684510', '12', '2007', '000');
		$payment->setCardType('Visa');
		
		$result = $payment->authorize();
		$this->assertTrue($result instanceof TransactionResult);
		
		$this->details->amount->set($this->details->amount->get() * 2);		
		$this->details->gatewayTransactionID->set($result->gatewayTransactionID->get());		
		
		$capture = new PaypalDirectPayment($this->details);
		$result = $capture->capture();
		
		$this->assertTrue($result instanceof TransactionError);
	}

	function testAuthorizationWith14PercentHigherCaptureAmount()
	{		
		$payment = new PaypalDirectPayment($this->details);
		$payment->setCardData('4522219712684510', '12', '2007', '000');
		$payment->setCardType('Visa');
		
		$result = $payment->authorize();
		$this->assertTrue($result instanceof TransactionResult);
		
		$this->details->amount->set(round($this->details->amount->get() * 1.14, 2));		
		$this->details->gatewayTransactionID->set($result->gatewayTransactionID->get());		
		
		$capture = new PaypalDirectPayment($this->details);
		$result = $capture->capture();
		
		$this->assertTrue($result instanceof TransactionResult);
	}

	function testAuthorizationWithPartialCaptureAmount()
	{		
		$payment = new PaypalDirectPayment($this->details);
		$payment->setCardData('4522219712684510', '12', '2007', '000');
		$payment->setCardType('Visa');
		
		$result = $payment->authorize();
		$this->assertTrue($result instanceof TransactionResult);
		
		$this->details->amount->set(round($this->details->amount->get() * 0.7, 2));		
		$this->details->gatewayTransactionID->set($result->gatewayTransactionID->get());		
		
		$capture = new PaypalDirectPayment($this->details);
		$result = $capture->capture();
		
		$this->assertTrue($result instanceof TransactionResult);
	}

	function testAuthorizationWithTwoCaptures()
	{		
		$payment = new PaypalDirectPayment($this->details);
		$payment->setCardData('4522219712684510', '12', '2007', '000');
		$payment->setCardType('Visa');
		
		$result = $payment->authorize();
		$this->assertTrue($result instanceof TransactionResult);
		
		// first capture - 70%
		$amount = $this->details->amount->get();
		$this->details->amount->set(round($amount * 0.7, 2));		
		$this->details->gatewayTransactionID->set($result->gatewayTransactionID->get());		
		
		$capture = new PaypalDirectPayment($this->details);
		$result = $capture->capture();
		
		$this->assertTrue($result instanceof TransactionResult);
		
		// second capture - 25%
		$this->details->amount->set(round($amount * 0.25, 2));		
		
		$capture = new PaypalDirectPayment($this->details);
		$result = $capture->capture();

		$this->assertTrue($result instanceof TransactionResult);
		
		// third capture - +17%
		$this->details->amount->set(round($amount * 0.12, 2));		
		
		$capture = new PaypalDirectPayment($this->details);
		$result = $capture->capture();

		$this->assertTrue($result instanceof TransactionResult);

		// fourth capture attempt should fail, because we have captured all allocated funds already
		$this->details->amount->set(round($amount * 0.2, 2));		
		
		$capture = new PaypalDirectPayment($this->details);
		$result = $capture->capture();

		$this->assertTrue($result instanceof TransactionError);
	}

	function testVoidAuthorizedTransaction()
	{
		$payment = new PaypalDirectPayment($this->details);
		$payment->setCardData('4522219712684510', '12', '2007', '000');
		$payment->setCardType('Visa');
		
		$result = $payment->authorize();
		$this->details->gatewayTransactionID->set($result->gatewayTransactionID->get());		
		
		$void = new PaypalDirectPayment($this->details);
		$result = $void->void();

		$this->assertTrue($result instanceof TransactionResult);
	}

	function testVoidCapturedTransaction()
	{
		$payment = new PaypalDirectPayment($this->details);
		$payment->setCardData('4522219712684510', '12', '2007', '000');
		$payment->setCardType('Visa');
		
		$result = $payment->authorizeAndCapture();
		$this->details->gatewayTransactionID->set($result->gatewayTransactionID->get());		
		
		$void = new PaypalDirectPayment($this->details);
		$result = $void->void();

		$this->assertTrue($result instanceof TransactionError);		
	}

	function testVoidHalfCapturedTransaction()
	{
		$payment = new PaypalDirectPayment($this->details);
		$payment->setCardData('4522219712684510', '12', '2007', '000');
		$payment->setCardType('Visa');
		
		$result = $payment->authorize();
		
		// first capture - 70%
		$amount = $this->details->amount->get();
		$this->details->amount->set(round($amount * 0.7, 2));		
		$this->details->gatewayTransactionID->set($result->gatewayTransactionID->get());				
		$capture = new PaypalDirectPayment($this->details);
		$result = $capture->capture();		
		
		// void the whole transaction
		$void = new PaypalDirectPayment($this->details);
		$result = $void->void();
		$this->assertTrue($result instanceof TransactionResult);

		// attempt a second capture - 30%
		$this->details->amount->set(round($amount * 0.3, 2));		
		
		$capture = new PaypalDirectPayment($this->details);
		$result = $capture->capture();

		$this->assertTrue($result instanceof TransactionError);		
	}
	
	function testUnsupportedCurrency()
	{
		$this->details->currency->set('LTL');
		$payment = new PaypalDirectPayment($this->details);
		$payment->setCardData('4522219712684510', '12', '2007', '000');
		$payment->setCardType('Visa');
		
		$result = $payment->authorizeAndCapture();

		$this->assertTrue($result instanceof TransactionError);		
	}

	function testAllCurrencies()
	{
		foreach (PaypalDirectPayment::getSupportedCurrencies() as $currency)
		{
			$this->details->currency->set($currency);
			$this->details->invoiceID->set(rand(1,1000000));
			$this->details->amount->set(1000);
			$payment = new PaypalDirectPayment($this->details);
			$payment->setCardData('4522219712684510', '12', '2007', '000');
			$payment->setCardType('Visa');
			
			$result = $payment->authorizeAndCapture();
			
			$this->assertTrue($result instanceof TransactionResult);					
		}	
	}	
}

?>