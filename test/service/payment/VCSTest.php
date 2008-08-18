<?php

include_once('PaymentTest.php');
ClassLoader::import('library.payment.method.VCS');

/**
 *
 * @package library.payment.test
 * @author Integry Systems
 */
class VCSTest extends PaymentTest
{
	private function getPaymentHandler()
	{
		$payment = new VCS($this->details);
		$payment->setConfigValue('account', 111);
		$payment->setConfigValue('description', 'test');
		$payment->setSiteUrl('http://localhost');

		return $payment;
	}

	function testPayment()
	{
		$payment = $this->getPaymentHandler();
		$postParams = $payment->getPostParams();

		$data['p1'] = $postParams['p1'];
		$data['p2'] = rand(1, 100000);

		$data['p3'] = '123456  APPROVED';

		$data['p5'] = 'Cardholder name';
		$data['p6'] = $postParams['p4'];
		$data['p7'] = 'Visa';
		$data['p8'] = $postParams['p3'];
		$data['p9'] = $postParams['p'];
		$data['test'] = true;

		$notify = $payment->notify($data);

		$this->assertEquals($notify->gatewayTransactionID->get(), $data['p2']);
		$this->assertEquals($notify->amount->get(), $data['p6']);
		$this->assertEquals($notify->getTransactionType(), TransactionResult::TYPE_SALE);
	}
}

?>