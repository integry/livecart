<?php

include_once('phppaypalpro/paypal_base.php');

class PaypalCommon
{
	/**
	 *	Get AVS (address verification) status by PayPal processor AVS code
	 *
	 *	First value is address verification and the second is ZIP verification
	 *	true - verification passed
	 *	false - verification failed
	 *	null - verification unavailable
	 *
	 *	@return bool
	 */
	public static function getAVSbyCode($code)
	{
		$avs = array(
			
					'A' => array(true, false),
					
					'B' => array(true, false),
					
					'C' => array(false, false),
					
					'D' => array(true, true),
					
					'E' => array(null, null),
					
					'F' => array(true, true),
					
					'G' => array(null, null),
					
					'I' => array(null, null),
					
					'N' => array(false, false),
					
					'P' => array(false, true),
					
					'R' => array(null, null),
					
					'S' => array(null, null),
					
					'U' => array(null, null),
					
					'W' => array(false, true),
					
					'X' => array(true, true),
					
					'Y' => array(true, true),
					
					'Z' => array(false, true),
		
				);		
	
		if (isset($avs[$code]))
		{
			return $avs[$code];
		}
		else
		{
			return array(null, null);
		}	
	}
	
	/**
	 *	Get card security code verification status by Paypal code
	 *
	 *	true - verification passed
	 *	false - verification failed
	 *	null - verification unavailable
	 *
	 *	@return bool
	 */
	public static function getCVVByCode($code)
	{
		if ('N' == $code)
		{
			return false;
		}
		else if ('M' == $code)
		{
			return true;
		}
		else
		{
			return null;
		}		
	}
	
	public static function getSupportedCurrencies()
	{
		return array('CAD', 'EUR', 'GBP', 'USD', 'JPY', 'AUD', 'NZD', 'CHF', 'HKD', 'SGD', 'SEK', 'DKK', 'PLN', 'NOK', 'HUF', 'CZK');
	}
	
	public static function isCurrencySupported($currencyCode)
	{
		if (array_search($currencyCode, self::getSupportedCurrencies()) !== false)
		{
			return true;
		}
	}
}

?>