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

}

?>