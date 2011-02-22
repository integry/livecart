<?php

/**
 * YahooCurrencyRateSource
 * @author Integry Systems
 */

ClassLoader::import('application.model.currencyrate.CurrencyRateSource');

class YahooCurrencyRateSource extends CurrencyRateSource
{

	public function getSourceName()
	{
		return 'Yahoo';
	}

	protected function fetchRates()
	{
		// http://download.finance.yahoo.com/d/quotes.csv?s=[From Currency][To Currency]=X&...&s=[From Currency][To Currency]=X&f=l1&e=.cs
		$this->rates = array();
		$pairs = array();
		foreach($this->allCurrencyCodes as $currencyCode)
		{
			$pairs[] = 's='.$this->baseCurrencyCode.$currencyCode.'=X';
		}
		if (count($pairs) == 0)
		{
			return;
		}
		$url = "http://download.finance.yahoo.com/d/quotes.csv?". implode("&", $pairs). "&e=.cs&f=l1";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($ch);
		curl_close($ch);
		$chunks= explode("\n", $result);
		foreach($chunks as &$item)
		{
			$item = trim($item);
			if (!is_numeric($item))
			{
				$item = null;
			}
		}
		$chunks = array_filter($chunks); // remove pairs with value null (most likely error text row!);
		if (count($chunks) != count($this->allCurrencyCodes)) // didnt got all rates
		{
			return;
		}
		$nr = 0;
		foreach($this->allCurrencyCodes as $currencyCode)
		{
			$this->rates[$currencyCode] = $chunks[$nr++];
		}
	}
}

?>