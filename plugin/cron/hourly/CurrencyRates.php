<?php

/**
 * @author Integry Systems
 */
class CurrencyRates extends CronPlugin
{

	public function isExecutable()
	{
		return true;
	}

	public function process()
	{
		$config = $this->application->getConfig();
		if (!$config->get('CURRENCY_RATE_UPDATE'))
		{
			return;
		}

		$cache = $this->application->getCache();
		$currencyRateUpdateTs = $cache->get('currencyRateUpdateTs', 0);
		$interval= $config->get('CURRENCY_RATE_UPDATE_INTERVAL');
		if (time() - (3600 * $interval) < $currencyRateUpdateTs)
		{
			return;
		}

		$dataSourceName = $config->get('CURRENCY_DATA_SOURCE');
		$defaultCurrencyCode = $this->application->getDefaultCurrencyCode();
		$currencyArray = $this->application->getCurrencyArray(true);
		ClassLoader::import('application.model.currencyrate.'.$dataSourceName);
		$source = new $dataSourceName($defaultCurrencyCode, $currencyArray);
		foreach($currencyArray as $currencyCode)
		{
			$rate = $source->getRate($currencyCode);
			if ($rate != null)
			{
				$currency = Currency::getInstanceById($currencyCode);
				$currency->rate->set($rate);
				$currency->lastUpdated->set(date('Y-m-d H:i:s', time()));
				$currency->save();
			}
		}

		$cache->set('currencyRateUpdateTs', time());
	}
}

?>