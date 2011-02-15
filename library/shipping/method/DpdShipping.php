<?php

include_once(dirname(__file__) . '/../ShippingRateCalculator.php');
include_once(dirname(__file__) . '/../library/dpd/DpdParser.php');
include_once(dirname(__file__) . '/../library/dpd/DpdZone.php');


/**
 * @package library.shipping.method
 * @author Integry Systems
 */
class DpdShipping extends ShippingRateCalculator
{
	private static $names = array(
		'riga' => 'Rīga',
		'major_city' => 'Lielākās pilsētas un novadi',
		'latvia' => 'Pārējā Latvijas teritorija',
		'baltic' => 'Lietuva Igaunija',
	);

	public function getProviderName()
	{
		return 'DPD';
	}

	public function getAllRates()
	{
		return $this->getRates();
	}

	public function getRates()
	{
		$result = new ShippingRateSet();
		try {
			Currency::getInstanceById('LVL');
		} catch(ARNotFoundException $e) {
			return $result;
		}
		$cacheKey = $this->getParserCacheKey();
		$application = ActiveRecordModel::getApplication();
		$cache = $application->getCache();
		$data = $cache->get($cacheKey);
		if (!$data || !is_array($data))
		{
			$parser=new DpdParser();
			$data = $parser->fetch();
			$cache->set($cacheKey, $data);
		}

		$zone = new DpdZone($this->destCity, $this->destZip, $this->destCountry);
		$zoneName = $zone->getZoneName();
		if (!$zoneName)
		{
			return $result;
		}

		$hasFoundPrice = false;
		$foundPrice = null;
		$this->weight = 1101;

		$currentWeight = 0;
		foreach($data as $weight=>$prices)
		{
			if ($weight == 'extra')
			{
				continue;
			}
			if ($weight < $this->weight)
			{
				$foundPrice = $prices;
				$currentWeight = $weight;
			}
			else
			{
				$hasFoundPrice = true;
				break; // are sorted by weight.
			}
		}

		if (!$hasFoundPrice && array_key_exists('extra', $data) && count($data['extra']))
		{
			$values = array_keys($data['extra']);
			foreach($values as $k=>$v)
			{
				$values[$k] = str_replace('+','', $v);
			}
			$weightDiff = $this->weight - $currentWeight;
			rsort($values);
			$foundValues = array();
			while($weightDiff > 0)
			{
				$flag = false;
				foreach($values as $value)
				{
					if ($weightDiff >= $value)
					{
						$foundValues[] = $value;
						$weightDiff = $weightDiff-$value;
						$flag = true;
						break;
					}
				}
				if (!$flag)
				{
					$value = array_pop($values);
					$foundValues[] = $value;
					$weightDiff = $weightDiff - $value;
				}
			}
			foreach ($foundValues as $value)
			{
				if (array_key_exists($zoneName, $foundPrice) && $foundPrice[$zoneName] !== null)
				{
					$foundPrice[$zoneName] += $data['extra']['+'.$value][$zoneName];
					$hasFoundPrice = true;
				}
			}
		}

		if ($hasFoundPrice)
		{
			if (array_key_exists($zoneName, $foundPrice) && $foundPrice[$zoneName] !== null)
			{
				$r = new ShippingRateResult();
				$r->setServiceName(self::$names[$zoneName]);
				$r->setCost($foundPrice[$zoneName], 'LVL');
				$r->setClassName(get_class($this));
				$r->setProviderName($this->getProviderName());
				$result->add($r);
			}
		}
		return $result;
	}


	private function getParserCacheKey()
	{
		//$config = ActiveRecordModel::getApplication()->getConfig();
		return array('shipping_method_dpd', md5(
			serialize(
				array(
					date('Y-m') // recheck every month
					// ..
				)
			)
		));
	}
}

?>