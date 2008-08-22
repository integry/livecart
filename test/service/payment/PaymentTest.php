<?php

/*
include_once('simpletest/unit_tester.php');
include_once('unittest/UnitTest.php');
include_once('simpletest/reporter.php');
*/

include_once 'Initialize.php';
ClassLoader::import('library.payment.TransactionDetails');

/**
 *
 * @package library.payment.test
 * @author Integry Systems
 */
class PaymentTest extends UnitTest
{
	public function tearDown()
	{
	}

	public function setUp()
	{
		$details = new TransactionDetails();
		$details->firstName->set('Rinalds');
		$details->lastName->set('Uzkalns');
		$details->address->set('Taikos 259-55');
		$details->city->set('Vilnius');
		$details->state->set('Vilnius');
		$details->country->set('LT');
		$details->postalCode->set('05214');

		$details->shippingFirstName->set('Rinalds');
		$details->shippingLastName->set('Uzkalns');
		$details->shippingAddress->set('Vytenio 50-305');
		$details->shippingCity->set('Vilnius');
		$details->shippingState->set('Vilnius');
		$details->shippingCountry->set('LT');
		$details->shippingPostalCode->set('05214');

		$details->phone->set('+370-66666666');
		$details->email->set('test@integry.net');

		$details->clientID->set('1');

		if (empty($_SERVER['REMOTE_ADDR']))
		{
			$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		}

		$details->ipAddress->set($_SERVER['REMOTE_ADDR']);

		$details->invoiceID->set(rand(1, 10000000));

		$details->amount->set('21.17');
		$details->currency->set('USD');
		$details->description->set('LiveCart Order');

		$this->details = $details;
	}

	public function getUsedSchemas()
	{
		return array();
	}
}

?>