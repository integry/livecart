<?php

include_once(dirname(__file__) . '/../ShippingRateCalculator.php');

/**
 *
 * @package library.shipping.method
 * @author Integry Systems
 */
class FedexShipping extends ShippingRateCalculator
{
	private $service;

	private static $names = array(
		'PRIORITYOVERNIGHT' => 'FedEx Priority Overnight',
		'STANDARDOVERNIGHT' => ' FedEx Standard Overnight',
		'FIRSTOVERNIGHT' => 'FedEx First Overnight',
		'FEDEX2DAY' => 'FedEx 2 Day',
		'FEDEXEXPRESSSAVER' => 'FedEx Express Saver',
		'INTERNATIONALPRIORITY' => 'FedEx International Priority',
		'INTERNATIONALECONOMY' => 'FedEx International Economy',
		'INTERNATIONALFIRST' => 'FedEx International First',
		'FEDEX1DAYFREIGHT' => 'FedEx Overnight Freight',
		'FEDEX2DAYFREIGHT' => 'FedEx 2 day Freight',
		'FEDEX3DAYFREIGHT' => 'FedEx 3 day Freight',
		'FEDEXGROUND' => 'FedEx Ground',
		'GROUNDHOMEDELIVERY' => 'FedEx Home Delivery');
		//'INTERNATIONALPRIORITY FREIGHT' => ''
		//'INTERNATIONALECONOMY FREIGHT' => ''
		//'EUROPEFIRSTINTERNATIONALPRIORITY' => ''

	public function getProviderName()
	{
		return 'FedEx';
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
		include_once(dirname(__file__) . '/../library/fedex/fedex.php');

		$fedex = new Fedex();

		$fedex->setServer($this->getConfigValue('apiUrl', 'https://gatewaybeta.fedex.com/GatewayDC'));

		$fedex->setAccountNumber($this->getConfigValue('accountNumber'));
		$fedex->setMeterNumber($this->getConfigValue('meterNumber'));
		$fedex->setCarrierCode('FDXE');
		$fedex->setDropoffType('REGULARPICKUP');
		$fedex->setPackaging('YOURPACKAGING');
		$fedex->setOriginStateOrProvinceCode($this->sourceState);
		$fedex->setOriginPostalCode($this->sourceZip);
		$fedex->setOriginCountryCode($this->sourceCountry);
		$fedex->setDestStateOrProvinceCode(in_array($this->destCountry, array('US', 'CA')) ? $this->destState : '');
		$fedex->setDestPostalCode($this->destZip);
		$fedex->setDestCountryCode($this->destCountry);
		$fedex->setPayorType('SENDER');

		// get weight in pounds/ounces
		$weight = $this->weight * 1000;
		$pounds = round($weight / 453.59237, 3);
		$fedex->setWeightUnits('LBS');
		$fedex->setWeight($pounds);

		$enabledServices = $this->getConfigValue('enabledServices', null);
		$price = $fedex->getPrice();

		// success
		if (isset($price['FDXRATEAVAILABLESERVICESREPLY'][0]['ENTRY']))
		{
			$rates = $price['FDXRATEAVAILABLESERVICESREPLY'][0]['ENTRY'];
			$result = new ShippingRateSet();

			foreach ($rates as $price)
			{
				$r = new ShippingRateResult();
				$code = $price['SERVICE'][0]['VALUE'];

				if (!is_array($enabledServices) || isset($enabledServices[$code]))
				{
					$name = self::$names[$code];
					if (isset($price['DELIVERYDATE'][0]['VALUE']))
					{
						$date = $price['DELIVERYDATE'][0]['VALUE'];
						$name = $name . ' (' . $date . ')';
					}

					$r->setServiceName($name);

					$cost = $price['ESTIMATEDCHARGES'][0]['DISCOUNTEDCHARGES'][0]['NETCHARGE'][0]['VALUE'];
					$currency = $price['ESTIMATEDCHARGES'][0]['CURRENCYCODE'][0]['VALUE'];
					$r->setCost($cost, $currency);
					$r->setClassName(get_class($this));
					$r->setProviderName($this->getProviderName());
					$result->add($r);
				}
			}
		}
		// error
		else
		{
			$result = new ShippingRateError($price['FDXRATEAVAILABLESERVICESREPLY'][0]['ERROR']);
		}

		$result->setRawResponse($price);

		return $result;
	}
}

?>