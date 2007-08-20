<?php

include_once('unittest/UTStandalone.php');

include_once('PaymentTest.php');
include_once(dirname(__file__) . '/../method/cc/PaypalDirectPayment.php');

class TestPaypalDirectPayment extends PaymentTest
{
	private function getPaymentHandler()
	{
		$payment = new PaypalDirectPayment($this->details);
		$payment->setConfigValue('username', 'sandbox_api1.integry.net');
		$payment->setConfigValue('password', '9AURF7SPQCEYCDXV');
		$payment->setConfigValue('signature', 'AeQ618dBMNS1kVFZwUIitcve-k.dAT5pnzBekoPUhcIj1J5p65ZAR8Pu');
		$payment->setCardData('4522219712684510', '12', '2007', '000');
		$payment->setCardType('Visa');
		
		return $payment;
	}

	function testInvalidCard()
	{
		$payment = $this->getPaymentHandler();
		$payment->setCardData('5522219712684510', '12', '2007', '000');
		$payment->setCardType('Visa');
		
		$result = $payment->authorizeAndCapture();
		
		$this->assertTrue($result instanceof TransactionError);		
	}

	function testAuthorizeAndCapture()
	{		
		$this->details->amount->set('321.17');
		$payment = $this->getPaymentHandler();
		
		$result = $payment->authorizeAndCapture();
		
		$this->assertTrue($result instanceof TransactionResult);
	}	
	
	function testAuthorizationWithSeparateCapture()
	{		
		$payment = $this->getPaymentHandler();
		
		$result = $payment->authorize();
		$this->assertTrue($result instanceof TransactionResult);
		
		$this->details->gatewayTransactionID->set($result->gatewayTransactionID->get());		
		
		$capture = $this->getPaymentHandler();
		$result = $capture->capture();
		
		$this->assertTrue($result instanceof TransactionResult);
	}	
	
	function testAuthorizationWithHugeCaptureAmount()
	{		
		$payment = $this->getPaymentHandler();
		
		$result = $payment->authorize();
		$this->assertTrue($result instanceof TransactionResult);
		
		$this->details->amount->set($this->details->amount->get() * 2);		
		$this->details->gatewayTransactionID->set($result->gatewayTransactionID->get());		
		
		$capture = $this->getPaymentHandler();
		$result = $capture->capture();
		
		$this->assertTrue($result instanceof TransactionError);
	}

	function testAuthorizationWith14PercentHigherCaptureAmount()
	{		
		$payment = $this->getPaymentHandler();
		
		$result = $payment->authorize();
		$this->assertTrue($result instanceof TransactionResult);
		
		$this->details->amount->set(round($this->details->amount->get() * 1.14, 2));		
		$this->details->gatewayTransactionID->set($result->gatewayTransactionID->get());		
		
		$capture = $this->getPaymentHandler();
		$result = $capture->capture();
		
		$this->assertTrue($result instanceof TransactionResult);
	}

	function testAuthorizationWithPartialCaptureAmount()
	{		
		$payment = $this->getPaymentHandler();
		
		$result = $payment->authorize();
		$this->assertTrue($result instanceof TransactionResult);
		
		$this->details->amount->set(round($this->details->amount->get() * 0.7, 2));		
        $this->details->gatewayTransactionID->set($result->gatewayTransactionID->get());		
		
		$capture = $this->getPaymentHandler();
		$result = $capture->capture();
		
		$this->assertTrue($result instanceof TransactionResult);
	}

	function testAuthorizationWithTwoCaptures()
	{		
		$payment = $this->getPaymentHandler();
		
		$result = $payment->authorize();
		$this->assertTrue($result instanceof TransactionResult);
		
		// first capture - 70%
		$amount = $this->details->amount->get();
		$this->details->amount->set(round($amount * 0.7, 2));		
		$this->details->gatewayTransactionID->set($result->gatewayTransactionID->get());		
		
		$capture = $this->getPaymentHandler();
		$result = $capture->capture();
		
		$this->assertTrue($result instanceof TransactionResult);
		
		// second capture - 25%
		$this->details->amount->set(round($amount * 0.25, 2));		
		
		$capture = $this->getPaymentHandler();
		$result = $capture->capture();

		$this->assertTrue($result instanceof TransactionResult);
		
		// third capture - +17%
		$this->details->amount->set(round($amount * 0.12, 2));		
		
		$capture = $this->getPaymentHandler();
		$result = $capture->capture();

		$this->assertTrue($result instanceof TransactionResult);

		// fourth capture attempt should fail, because we have captured all allocated funds already
		$this->details->amount->set(round($amount * 0.2, 2));		
		
		$capture = $this->getPaymentHandler();
		$result = $capture->capture();

		$this->assertTrue($result instanceof TransactionError);
	}

	function testVoidAuthorizedTransaction()
	{
		$payment = $this->getPaymentHandler();
		
		$result = $payment->authorize();
		$this->details->gatewayTransactionID->set($result->gatewayTransactionID->get());		
		
		$void = $this->getPaymentHandler();
		$result = $void->void();

		$this->assertTrue($result instanceof TransactionResult);
	}

	function testVoidCapturedTransaction()
	{
		$payment = $this->getPaymentHandler();
		
		$result = $payment->authorizeAndCapture();
		$this->details->gatewayTransactionID->set($result->gatewayTransactionID->get());		
		
		$void = $this->getPaymentHandler();
		$result = $void->void();

		$this->assertTrue($result instanceof TransactionError);		
	}

	function testVoidHalfCapturedTransaction()
	{
		$payment = $this->getPaymentHandler();
		
		$result = $payment->authorize();
		
		// first capture - 70%
		$amount = $this->details->amount->get();
		$this->details->amount->set(round($amount * 0.7, 2));		
		$this->details->gatewayTransactionID->set($result->gatewayTransactionID->get());				
		$capture = $this->getPaymentHandler();
		$result = $capture->capture();		
		
		// void the whole transaction
		$void = $this->getPaymentHandler();
		$result = $void->void();
		$this->assertTrue($result instanceof TransactionResult);

		// attempt a second capture - 30%
		$this->details->amount->set(round($amount * 0.3, 2));		
		
		$capture = $this->getPaymentHandler();
		$result = $capture->capture();

		$this->assertTrue($result instanceof TransactionError);		
	}
	
	function testUnsupportedCurrency()
	{
		$this->details->currency->set('LTL');
		$payment = $this->getPaymentHandler();
		
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
			$payment = $this->getPaymentHandler();
			
			$result = $payment->authorizeAndCapture();
			
			$this->assertTrue($result instanceof TransactionResult);					
		}	
	}	
}

?>