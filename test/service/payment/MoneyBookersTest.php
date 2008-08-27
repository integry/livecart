<?php

include_once('PaymentTest.php');
ClassLoader::import('library.payment.method.MoneyBookers');

/**
 *
 * @package library.payment.test
 * @author Integry Systems
 */
class MoneyBookersTest extends PaymentTest
{
	private function getPaymentHandler()
	{
		$payment = new MoneyBookers($this->details);
		$payment->setConfigValue('email', 'payments@livecart.com');
		$payment->setConfigValue('description', 'test');
		$payment->setConfigValue('secretWord', 'test');
		$payment->setSiteUrl('http://localhost');

		return $payment;
	}

	function testPayment()
	{
		$payment = $this->getPaymentHandler();
		$postParams = $payment->getPostParams();

		$data['pay_to_email'] = $payment->getConfigValue('email');
		$data['pay_from_email'] = 'customer@customer.com';
		$data['merchant_id'] = rand(1, 100000);
		$data['transaction_id'] = 666;
		$data['mb_transaction_id'] = 111;
		$data['status'] = 2;
		$data['mb_amount'] = 20;
		$data['mb_currency'] = 'USD';

		// set invalid MD5 checksum
		$data['md5sig'] = 'JUNK';
		$this->assertIsA($payment->notify($data), 'TransactionError');

		// valid data
		$data['md5sig'] = strtoupper(md5($data['merchant_id'] . $data['transaction_id'] . strtoupper(md5($payment->getConfigValue('secretWord'))) . $data['mb_amount'] . $data['mb_currency'] . $data['status']));
		$result = $payment->notify($data);
		$this->assertIsA($result, 'TransactionResult');
	}
}

?>