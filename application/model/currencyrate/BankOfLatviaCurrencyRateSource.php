<?php

/**
 * BankOfLatviaCurrencyRateSource
 * @author Integry Systems
 */
 class BankOfLatviaCurrencyRateSource extends CurrencyRateSource
{
	public function getSourceName()
	{
		return 'Bank of Latvia';
	}

	protected function fetchRates()
	{
		$this->rates = array();
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://www.bank.lv/excel/valkurlv.php');
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($ch);
		curl_close($ch);
		$rows = explode("\n", $result);
		foreach ($rows as $row)
		{
			if (preg_match('/(?P<count>[0-9]*)\s*(?P<currency>[a-z]+)\s*(?P<rate>[0-9\.\,]+)/i',trim($row), $m))
			{
				$m['rate'] = floatval(str_replace(',','.',$m['rate']));
				if ($m['count'])
				{
					$m['rate'] = $m['rate'] / $m['count'];
				}
				$this->rates[$m['currency']] = $m['rate'];
			}
		}

		if ($this->baseCurrencyCode != 'LVL')
		{
			if (!array_key_exists($this->baseCurrencyCode, $this->rates))
			{
				return false; // base currency not found, can't convert.
			}
			$toBaseCurrencyRate = $this->rates[$this->baseCurrencyCode];
			foreach($this->rates as $i => $rate)
			{
				$this->rates[$i] =  $rate/ ($toBaseCurrencyRate);
			}
			$this->rates['LVL'] = 1 / $toBaseCurrencyRate;
		}
	}
}

?>