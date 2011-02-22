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

	public static function getCurrencyRateSourceList()
	{
		$list = array();
		foreach (new DirectoryIterator(ClassLoader::getRealPath('application.model.currencyrate')) as $item)
		{
			if ($item->isDot())
			{
				continue;
			}
			$fn = $item->getFileName();
			$cn = substr($fn, 0, -4);

			ClassLoader::import('application.model.currencyrate.'.$cn);
			if(!is_subclass_of($cn, __CLASS__))
			{
				continue;
			}
			$list[$cn] = $cn;
		}
		ksort($list);
		return $list ;
	}
}
?>