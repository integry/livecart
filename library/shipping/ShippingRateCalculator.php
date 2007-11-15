<?php

include_once('ShippingRateSet.php');
include_once('ShippingRateError.php');
include_once('ShippingRateResult.php');

/**
 *
 * @package library.shipping
 * @author Integry Systems 
 */
abstract class ShippingRateCalculator
{
	protected $destCountry;   
	protected $destZip;   
	protected $sourceCountry;   
	protected $sourceZip;   
	protected $weight;   
	
	protected $config = array();	
	
	public function setDestCountry($country)
	{
		$this->destCountry = $country;
	}

	public function setDestZip($zip)
	{
		$this->destZip = $zip;
	}

	public function setSourceCountry($country)
	{
		$this->sourceCountry = $country;
	}

	public function setSourceZip($zip)
	{
		$this->sourceZip = $zip;
	}

	public function setWeight($grams)
	{
		$this->weight = $grams;
	}
	
	public function setConfigValue($key, $value)
	{
		$this->config[$key] = $value;
	}
	
	public function getConfigValue($key, $defaultValue = '')
	{
		if (isset($this->config[$key]))
		{
			return $this->config[$key];
		}
		else
		{
			return $defaultValue;
		}
	}	
	
	/**
	 *  Return shipping rates for the particular service
	 */
	public abstract function getRates();

	/**
	 *  Return all available shipping rates for all available services
	 */
	public abstract function getAllRates();

	/**
	 *  Return shipping company name (for example: "USPS")
	 */
	public abstract function getProviderName();
}


?>