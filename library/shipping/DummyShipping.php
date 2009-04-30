<?php

include_once(dirname(__file__) . '/../ShippingRateCalculator.php');

/**
 * Template class for creating new real-time or complex shipping rate calculation modules
 *
 * Save your class file in the ./method directory with the same file name as class name
 * More info: http://doc.livecart.com/help/dev.plugin.shipping
 *
 * @package library.shipping.method
 * @author Integry Systems
 */
class DummyShipping extends ShippingRateCalculator
{
	public function getProviderName()
	{
		return 'Custom Shipping';
	}

	public function getAllRates()
	{
		return $this->getRates();
	}

	public function getRates()
	{
		// Weight in kg
		// $this->weight

		// shipping destination postal code
		// $this->destZip

		// shipping destination country
		// $this->destCountry

		$result = new ShippingRateSet();

		$r1->setServiceName('Fixed Cost Delivery');
		$r1->setCost(100, 'USD');
		$r1->setClassName(get_class($this));
		$r1->setProviderName($this->getProviderName());
		$result->add($r1);

		$r1->setServiceName('Calculated Cost Delivery');

		if ($this->weight < 10)
		{
			$cost = $this->weight * 2;
		}
		else if ($this->weight < 20)
		{
			$cost = $this->weight * 1.5;
		}
		else
		{
			$cost = ceil($this->weight / 10) * 10 * 1.5;
		}

		$r2->setCost($cost, 'USD');
		$r2->setClassName(get_class($this));
		$r2->setProviderName($this->getProviderName());
		$result->add($r2);

		return $result;
	}
}

?>