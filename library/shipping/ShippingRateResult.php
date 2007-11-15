<?php

include_once('ShippingResultInterface.php');

/**
 *
 * @package library.shipping
 * @author Integry Systems 
 */
class ShippingRateResult implements ShippingResultInterface
{
	protected $serviceID;
	protected $serviceName;
	protected $costAmount;
	protected $costCurrency;
	protected $className;
	protected $providerName;
	protected $rawResponse;
		
	public function setServiceID($id)
	{
		$this->serviceID = $id;	
	}
	
	public function setServiceName($name)
	{
		$this->serviceName = $name;			
	}

	public function setCost($amount, $currency = null)
	{
		$this->costAmount = $amount;
		
		if (!is_null($currency))
		{
			$this->costCurrency = $currency;
		}
	}
	
	public function getClassName()
	{
		return $this->className;
	}

	public function setClassName($className)
	{
		$this->className = $className;
	}

	public function setRawResponse($response)
	{
		$this->rawResponse = $response;   
	}

	public function getServiceName()
	{
		return $this->serviceName;
	}

	public function getProviderName()
	{
		return $this->providerName;
	}

	public function setProviderName($name)
	{
		$this->providerName = $name;
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
		$result['providerName'] = $this->providerName;
		$result['className'] = $this->className;
		$result['costAmount'] = $this->costAmount;
		$result['costCurrency'] = $this->costCurrency;  
		return $result;
	}
}

?>