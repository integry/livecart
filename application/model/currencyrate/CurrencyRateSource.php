<?php

/**
 * CurrencyRateSource
 * @author Integry Systems
 */
 
abstract class CurrencyRateSource
{
	abstract public function getSourceName();

	abstract protected function fetchRates();

	protected $baseCurrencyCode;

	protected $allCurrencyCodes;

	protected $rates = null;

	public function __construct($baseCurrencyCode, $allCurrencyCodes)
	{
		$this->baseCurrencyCode = strtoupper($baseCurrencyCode);
		$this->allCurrencyCodes = $allCurrencyCodes;
	}

	public function getRate($currencyCode)
	{
		if ($this->rates === null)
		{
			$this->fetchRates();
		}
		return array_key_exists($currencyCode, $this->rates) ? $this->rates[$currencyCode] : null;
	}

	public function getAllCurrencyCodes()
	{
		return $this->allCurrencyCodes;
	}

	public static function getCurrencyRateSourceList()
	{
		$list = array();
		foreach (new DirectoryIterator($this->config->getPath('application/model/currencyrate')) as $item)
		{
			if ($item->isDot())
			{
				continue;
			}
			$fn = $item->getFileName();
			$cn = substr($fn, 0, -4);

						if(!is_subclass_of($cn, __CLASS__))
			{
				continue;
			}
			$list[$cn] = $cn;
		}
		ksort($list);
		return $list ;
	}

	public static function getInstance($application, $defaultCurrencyCode=null, $currencyArray=null, $dataSourceName=null)
	{
		if ($defaultCurrencyCode === null)
		{
			$defaultCurrencyCode = $application->getDefaultCurrencyCode();
		}
		if ($currencyArray === null)
		{
			$currencyArray = $application->getCurrencyArray(true);
		}
		if ($dataSourceName === null)
		{
			$dataSourceName = $application->getConfig()->get('CURRENCY_DATA_SOURCE');
		}
				$source = new $dataSourceName($defaultCurrencyCode, $currencyArray);

		return $source;
	}
}
?>