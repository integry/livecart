<?php

include_once(dirname(__file__) . '/../ShippingRateCalculator.php');

/**
 *
 * @package library.shipping.method
 * @author Integry Systems
 */
class UpsShipping extends ShippingRateCalculator
{
	private $service;

	private static $names = array(
		'UPS01' => 'UPS Next Day Air',
		'UPS02' => 'UPS Second Day Air',
		'UPS03' => 'UPS Ground',
		'UPS07' => 'UPS Worldwide Express',
		'UPS08' => 'UPS Worldwide Expedited',
		'UPS11' => 'UPS Standard',
		'UPS12' => 'UPS Three-Day Select',
		'UPS13' => 'Next Day Air Saver',
		'UPS14' => 'UPS Next Day Air Early AM',
		'UPS54' => 'UPS Worldwide Express Plus',
		'UPS59' => 'UPS Second Day Air AM',
		'UPS65' => 'UPS Saver');

	public function getProviderName()
	{
		return 'UPS';
	}

	public function getAllServices()
	{
		return self::$names;
	}

	public function getAllRates()
	{
		return $this->getRates();
	}

	public function getRates()
	{
		$result = new ShippingRateSet();
		foreach ($this->getConfigValue('enabledServices', array()) as $key => $value)
		{
			$res = $this->getRate($key);

			if ($res)
			{
				$r = new ShippingRateResult();
				$r->setServiceName(self::$names[$key]);
				$r->setCost($res['MONETARYVALUE'], 'USD', $res['CURRENCYCODE']);
				$r->setClassName(get_class($this));
				$r->setProviderName($this->getProviderName());
				$result->add($r);
			}
		}

		$result->setRawResponse(null);

		return $result;
	}

	public function getRate($service)
	{
		include_once(dirname(__file__) . '/../library/ups/upsRate.php');

		$weight = $this->weight * 1000;
		$pounds = round($weight / 453.59237, 3);

		$ups = new upsRate();
		$ups->setCredentials($this->getConfigValue('accessKey'), $this->getConfigValue('user'), $this->getConfigValue('password'), '');
		return $ups->getRate($this->sourceZip, $this->destZip, substr($service, 3), $pounds, $this->sourceCountry, $this->destCountry);
	}
}

?>