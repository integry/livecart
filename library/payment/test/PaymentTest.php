<?php

include_once('simpletest/unit_tester.php');
include_once('unittest/UnitTest.php');
include_once('simpletest/reporter.php');

include_once('../TransactionDetails.php');

class PaymentTest extends UnitTest
{
	function setUp()
	{
		$details = new TransactionDetails();
		$details->firstName->set('Rinalds');
		$details->lastName->set('Uzkalns');
		$details->address->set('Taikos 259-55');
		$details->city->set('Vilnius');
		$details->state->set('CA');
		$details->country->set('US');
		$details->postalCode->set('90210');								

		$details->shippingFirstName->set('Rinalds');
		$details->shippingLastName->set('Uzkalns');
		$details->shippingAddress->set('Vytenio 50-305');
		$details->shippingCity->set('Vilnius');
		$details->shippingState->set('Vilnius');
		$details->shippingCountry->set('LT');
		$details->shippingPostalCode->set('LT-05214');								

		$details->phone->set('+370-66666666');
		$details->email->set('sandbox@integry.net');								
				
		$details->clientID->set('22');
		$details->ipAddress->set($_SERVER['REMOTE_ADDR']);
		
		$details->invoiceID->set(rand(1, 10000000));
				
		$details->amount->set('21.17');
		$details->currency->set('USD');
		$details->description->set('LiveCart Order');								

		$this->details = $details;		
	}
}

?>