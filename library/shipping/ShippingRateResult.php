<?php

class ShippingRateResult
{
    protected $serviceName;
    protected $costAmount;
    protected $costCurrency;
    protected $rawResponse;
        
    public function setServiceName($name)
    {
        $this->serviceName = $name;            
    }

    public function setCost($amount, $currency)
    {
        $this->costAmount = $amount;
        $this->costCurrency = $currency;
    }
    
    public function setRawResponse($response)
    {
        $this->rawResponse = $response;   
    }

    public function getServiceName()
    {
        return $this->serviceName;
    }

    public function getCostAmount()
    {
        return $this->costAmount;
    }

    public function getCostCurrency()
    {
        return $this->costCurrency;
    }
    
    public function getRawResponse()
    {
        return $this->rawResponse;   
    }    
}

?>