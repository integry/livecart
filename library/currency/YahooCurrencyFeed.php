<?php

/**
 *
 * @package library/currency
 * @author Integry Systems 
 */
class YahooCurrencyFeed extends CurrencyRateFeed
{
	public static function getName()
	{
	  	return 'Yahoo!';
	}	  	  
	
	protected function getFeedUrl()
	{
	  	return 'http://download.finance.yahoo.com/d/quotes.csv?s=' . $this->baseCurrency .
		  	   $this->targetCurrency . '=X&f=sl1d1t1ba&e=.csv';
	}  
	
	protected function parseData($feedData)
	{
		$data = explode(',', $feedData);
		if (is_numeric($data[1]))
		{
		  	$this->setRate($data[1]);
		}  	
	}
}

?>