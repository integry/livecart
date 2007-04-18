<?php

include_once('ShippingResultInterface.php');

class ShippingRateResult implements ShippingResultInterface
{
    protected $serviceID;
	protected $serviceName;
    protected $costAmount;
    protected $costCurrency;
    protected $rawResponse;
        
    public function setServiceID($id)
    {
		$this->serviceID = $id;	
	}
	
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
    
    public function getServiceID()
    {
		return $this->serviceID;
	}
    
    public function getRawResponse()
    {
        return $this->rawResponse;   
    }    
    
    public function toArray()
    {
        $result = array();
        $result['serviceID'] = $this->serviceID;
        $result['serviceName'] = $this->serviceName;
        $result['costAmount'] = $this->costAmount;
        $result['costCurrency'] = $this->costCurrency;  
        return $result;
    }
}

?>