<?php

include_once('PaymentTest.php');
ClassLoader::import('library.payment.method.cc.ChronoPayGateway');

/**
 *
 * @package library.payment.test
 * @author Integry Systems
 */
class ChronoPayGatewayTest extends PaymentTest
{
	private function getPaymentHandler()
	{
		$payment = new ChronoPayGateway($this->details);
		$payment->setConfigValue('secret', 'LIvECArt098teSt7');
		$payment->setConfigValue('productid', '004284-0001-0001');

		$payment->setCardData('4296010582436758', '12', '2007', '123');

		return $payment;
	}

	function testInvalidCard()
	{
		$payment = $this->getPaymentHandler();
		$payment->setCardData('5522219712684510', '12', '2007', '000');

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

		$result->gatewayTransactionID->set('Test card');
		$this->details->gatewayTransactionID->set($result->gatewayTransactionID->get());

		$capture = $this->getPaymentHandler();
		$result = $capture->capture();

		$this->assertTrue($result instanceof TransactionResult);
	}

/*
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

	*/
}

?>