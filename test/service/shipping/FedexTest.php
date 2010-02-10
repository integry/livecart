<?php

include_once('TestShipping.php');
ClassLoader::import('library.shipping.method.FedexShipping');

/**
 *
 * @package library.shipping.test
 * @author Integry Systems
 */
class FedexTest extends TestShipping
{
	private function getHandler()
	{
		$fedex = new FedexShipping();
		$fedex->setConfigValue('accountNumber', '510087941');
		$fedex->setConfigValue('meterNumber', '1250347');

//		$fedex->setConfigValue('accountNumber', '236800164');
//		$fedex->setConfigValue('meterNumber', '101180614');

		$fedex->setSourceCountry('US');
		$fedex->setSourceState('OH');
		$fedex->setSourceZip('44333');

		return $fedex;
	}

	function testRates()
	{
		$fedex = $this->getHandler();
		$fedex->setDestCountry('US');
		$fedex->setDestState('CA');
		$fedex->setDestZip('90210');
		$fedex->setWeight(15);

		$rates = $fedex->getRates();
		$this->assertTrue($rates instanceof ShippingRateSet);
	}

	function testCanadaRates()
	{
		$fedex = $this->getHandler();
		$fedex->setDestCountry('CA');
		$fedex->setDestState('Quebec');
		$fedex->setDestZip('h9b1z8');
		$fedex->setWeight(1);

		$rates = $fedex->getRates();
		var_dump($rates);
		$this->assertTrue($rates instanceof ShippingRateSet);
	}

	function testInternationalRates()
	{
		$fedex = $this->getHandler();
		$fedex->setDestCountry('LV');
		$fedex->setDestState('RIX');
		$fedex->setDestZip('2000');
		$fedex->setWeight(1);

		$rates = $fedex->getRates();
		$this->assertTrue($rates instanceof ShippingRateSet);
	}

}

?>
