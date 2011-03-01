<?php

/**
 * @author Integry Systems
 */
ClassLoader::import('application.model.currencyrate.CurrencyRateSource');

class CurrencyRates extends CronPlugin
{
	public function isExecutable($interval)
	{
		if (parent::isExecutable($interval))
		{
			$config = $this->application->getConfig();
			if (!$config->get('CURRENCY_RATE_UPDATE'))
			{
				return false;
			}
			$currencyRateUpdateTs = $this->application->getCache()->get('currencyRateUpdateTs', 0);
			$interval= $config->get('CURRENCY_RATE_UPDATE_INTERVAL');
			if (time() - (3600 * $interval) < $currencyRateUpdateTs)
			{
				return false;
			}
			// only true if:
			//      isExecutable() now
			//  + is CURRENCY_RATE_UPDATE enabled
			//  + has passed CURRENCY_RATE_UPDATE_INTERVAL hours till last update
			return true;
		}

		return false;
	}

	public function process()
	{
		$source = CurrencyRateSource::getInstance($this->application);
		foreach($source->getAllCurrencyCodes() as $currencyCode)
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
		$this->application->getCache()->set('currencyRateUpdateTs', time());
	}
}

?>