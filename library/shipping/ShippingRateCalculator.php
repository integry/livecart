<?php

include_once('ShippingRateSet.php');
include_once('ShippingRateError.php');
include_once('ShippingRateResult.php');

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
    
    public abstract function getRates();

    public abstract function getAllRates();

    public abstract function getProviderName();
}


?>