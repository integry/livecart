<?php

class TestAuthorizeNet extends UnitTest
{
	function testAuthorization()
	{
		$details = new TransactionDetails();
		$details->firstName->set('Rinalds');
		$details->lastName->set('Uzkalns');
		$details->address->set('Vytenio 50-305');
		$details->city->set('Vilnius');
		$details->state->set('Vilnius');
		$details->country->set('LT');
		$details->postalCode->set('LT-05214');								

		$details->shippingFirstName->set('Rinalds');
		$details->shippingLastName->set('Uzkalns');
		$details->shippingAddress->set('Vytenio 50-305');
		$details->shippingCity->set('Vilnius');
		$details->shippingState->set('Vilnius');
		$details->shippingCountry->set('LT');
		$details->shippingPostalCode->set('LT-05214');								

		$details->phone->set('+370-66666666');
		$details->email->set('LT-05214');								
				
		$details->clientID->set('22');
		$details->ipAddress->set('217.147.38.82');								

		$details->invoiceID->set('666');
				
		$details->amount->set('123.45');
		$details->currency->set('USD');
		$details->description->set('LiveCart Order');								
		
		return $details;
	}	
}

?>