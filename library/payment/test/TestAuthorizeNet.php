<?php

include_once('unittest/UTStandalone.php');
include_once('PaymentTest.php');
include_once(dirname(__file__) . '/../method/cc/AuthorizeNet.php');

class TestAuthorizeNet extends PaymentTest
{
	private function getPaymentHandler()
	{
		$payment = new AuthorizeNet($this->details);
		$payment->setConfigValue('login', 'cnpdev4444');
		$payment->setConfigValue('transactionKey', '623P88zxd6Pj3Pz6');
		$payment->setConfigValue('gateway', 'https://test.authorize.net/gateway/transact.dll');
		
		$payment->setCardData('4007000000027', '12', '2007', '000');
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
	
	/**
	 *  Authorize.net doesn't allow the capture amount to exceed authorized amount
	 */
    function testAuthorizationWith5PercentHigherCaptureAmount()
	{		
		$payment = $this->getPaymentHandler();
		
		$result = $payment->authorize();
		$this->assertTrue($result instanceof TransactionResult);
		
		$this->details->amount->set(round($this->details->amount->get() * 1.05, 2));		
		$this->details->gatewayTransactionID->set($result->gatewayTransactionID->get());		
		
		$capture = $this->getPaymentHandler();
		$result = $capture->capture();
		
		$this->assertTrue($result instanceof TransactionError);
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
    
	/**
	 *  Authorize.net only allows one capture per authorization
	 */
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

		$this->assertTrue($result instanceof TransactionResult);		
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
		$this->details->currency->set('EUR');
		$payment = $this->getPaymentHandler();
		
		$result = $payment->authorizeAndCapture();

		$this->assertTrue($result instanceof TransactionError);		
	}	
}

?>