<?php

include_once('PaymentTest.php');

ClassLoader::import('library.payment.method.cc.BucksNet');

/**
 *
 * @package library.payment.test
 * @author Integry Systems
 */
class BucksNetTest extends PaymentTest
{
	private function getPaymentHandler()
	{
		$payment = new BucksNet($this->details);
		$payment->setConfigValue('traderID', 'TST2');
		$payment->setConfigValue('username', 'test');
		$payment->setConfigValue('password', 'test');
		$payment->setConfigValue('test', 'true');

		$payment->setCardData('*GOODCARD*', '12', '2010', '000');

		return $payment;
	}

	function testInvalidCard()
	{
		$payment = $this->getPaymentHandler();
		$payment->setCardData('*BADCARD*', '12', '2010', '000');

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
}

?>
