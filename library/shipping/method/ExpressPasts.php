<?php

include_once(dirname(__file__) . '/../ShippingRateCalculator.php');
include_once(dirname(__file__) . '/../library/expresspasts/ExpresspastsParser.php');
include_once(dirname(__file__) . '/../library/expresspasts/ExpresspastsZone.php');

/**
 *
 * @package library.shipping.method
 * @author Integry Systems
 */
class ExpressPasts extends ShippingRateCalculator
{

	public function getProviderName()
	{
		return 'ExpressPasts';
	}

	private static $names = array(
		'contract_economic' => 'Expresspasts x1 Ekonomisks', // ar līgumu
		'contract_standart' => 'Expresspasts x2 Standarts', // ar līgumu
		'economic' => 'Expresspasts x1 Ekonomisks',
		'standart' => 'Expresspasts x2 Standarts',
	);

	public function getAllRates()
	{
		if ($this->sourceCountry != 'LV')
		{
			// throw new ApplicationException('Expresspasts is available only in Latvia');
			return new ShippingRateSet();
		}
		try {
			Currency::getInstanceById('LVL');
		} catch(ARNotFoundException $e) {
			// throw new ApplicationException('Expresspasts requires LVL');
			return new ShippingRateSet();
		}
		$cacheKey = $this->getParserCacheKey();
		$application = ActiveRecordModel::getApplication();
		$cache = $application->getCache();
		$data = $cache->get($cacheKey);
		if (!$data || !is_array($data))
		{
			$parser=new ExpresspastsParser();
			$data = $parser->fetch();
			$cache->set($cacheKey, $data);
		}

		$config = $application->getConfig();
		$zone = new ExpresspastsZone($config->get('EXPRESSPASTS_FROM_LOCATION'), $this->destCity, $this->destZip);

		$return = new ShippingRateSet();
		foreach($data as $type => $typeData)
		{
			$foundPrice = null;
			foreach($typeData as $weight=>$prices)
			{
				if ($weight < $this->weight)
				{
					$foundPrice = $prices;
				}
				else
				{
					break; // are sorted by weight.
				}
			}

			if ($foundPrice)
			{
				$r = new ShippingRateResult();
				$r->setServiceName(self::$names[$type]);
				$r->setCost($foundPrice['zone'.(string)$zone], 'LVL');
				$r->setClassName(get_class($this));
				$r->setProviderName($this->getProviderName());
				$return->add($r);
			}
		}
		return $return;
	}

	public function getRates()
	{
		return $this->getAllRates();
	}

	private function getParserCacheKey()
	{
		$config = ActiveRecordModel::getApplication()->getConfig();

		return array('shipping_method_expresspasts', md5(
			serialize(
				array(
					$config->get('EXPRESSPASTS_CONTRACTUAL_CLIENT'),
					$config->get('EXPRESSPASTS_SERVICE_TYPE'),
					// $config->get('EXPRESSPASTS_FROM_LOCATION'),
					date('Y-m') // recheck every month?
				)
			)
		));
	}
}

?>