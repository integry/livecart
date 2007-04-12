<?php

include_once('ShippingResultInterface.php');
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
    
    public abstract function getRates();
}


?>