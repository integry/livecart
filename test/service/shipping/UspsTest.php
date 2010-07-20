<?php

include_once('TestShipping.php');
ClassLoader::import('library.shipping.method.UspsShipping');

/**
 *
 * @package library.shipping.test
 * @author Integry Systems
 */
class UspsTest extends TestShipping
{
	function testDomesticRates()
	{
		$usps = new UspsShipping();
		$usps->setUserId('550INTEG8147');
		$usps->setSourceCountry('US');
		$usps->setSourceZip('44106');
		$usps->setDestCountry('US');
		$usps->setDestZip('20770');
		$usps->setSize('REGULAR');
		$usps->setMachinable(true);
		$usps->setWeight(15);

		// first class
		$usps->setWeight(0.2);
		$usps->setService('First Class');
		$rates = $usps->getRates();
		$this->assertTrue($rates instanceof ShippingRateSet);

		$usps->setWeight(15);

		// priority
		$usps->setService('Priority');

		$usps->setContainer('');
		$rates = $usps->getRates();
		$this->assertTrue($rates instanceof ShippingRateSet);

		$usps->setContainer('Flat Rate Envelope');
		$rates = $usps->getRates();
		$this->assertTrue($rates instanceof ShippingRateSet);

		$usps->setContainer('Flat Rate Box');
		$rates = $usps->getRates();
		$this->assertTrue($rates instanceof ShippingRateSet);

		// express
		$usps->setService('Express');
		$rates = $usps->getRates();
		$this->assertTrue($rates instanceof ShippingRateSet);

		// parcel post
		$usps->setService('Parcel');
		$rates = $usps->getRates();
		$this->assertTrue($rates instanceof ShippingRateSet);

		// Bound Printed Matter
		$usps->setWeight(3);
		$usps->setService('BPM');
		$rates = $usps->getRates();
		$this->assertTrue($rates instanceof ShippingRateSet);

		// Media
		$usps->setWeight(3);
		$usps->setService('Media');
		$rates = $usps->getRates();
		$this->assertTrue($rates instanceof ShippingRateSet);

		// overweight package
		$usps->setWeight(50);
		$usps->setService('Media');
		$rates = $usps->getRates();
		$this->assertTrue($rates instanceof ShippingRateError);
	}

	function testPriorityNonFlatRate()
	{
		$usps = new UspsShipping();
		$usps->setUserId('550INTEG8147');
		$usps->setSourceCountry('US');
		$usps->setSourceZip('44106');
		$usps->setDestCountry('US');
		$usps->setDestZip('20770');
		$usps->setSize('REGULAR');
		$usps->setMachinable(true);
		$usps->setWeight(0.2);
		$usps->setService('Priority');

		// non-flat rate container
		$usps->setContainer('Variable');
		$this->assertTrue($usps->getRates() instanceof ShippingRateError);

		// no container at all
		$usps->setContainer('');
		$this->assertTrue($usps->getRates() instanceof ShippingRateSet);
	}

	public function testInternational()
	{
		$usps = new UspsShipping();
		$usps->setUserId('550INTEG8147');
		$usps->setSourceCountry('US');
		$usps->setSourceZip('90210');
		$usps->setDestCountry('LT');
		$usps->setSize('REGULAR');
		$usps->setMachinable(true);
		$usps->setWeight(15);
		$usps->setService('Package');

		$rates = $usps->getRates();

		$this->assertTrue($rates instanceof ShippingRateSet);

	}
}

?>