<?php

include_once(dirname(__file__) . '/ShippingResultInterface.php');

class ShippingRateSet implements ShippingResultInterface, IteratorAggregate
{
    protected $rates = array();
    protected $rawResponse;

    public function add(ShippingRateResult $rate)
    {
        $this->rates[] = $rate;
    }
    
    public function getRates()
    {
        return $this->rates;
    }
    
    public function get($id)
    {
        if (isset($this->rates[$id]))
        {
            return $this->rates[$id];
        }
        else
        {
            return null;
        }
    }
    
    public function getByServiceId($id)
    {
        foreach ($this->rates as $rate)
        {
            if ($rate->getServiceID() == $id)
            {
                return $rate;
            }
        }
    }
    
    public function setRawResponse($response)
    {
        $this->rawResponse = $response;
    }

    public function getRawResponse()
    {
        return $this->rawResponse;
    }
    
    public function merge(ShippingRateSet $rateSet)
    {
        foreach ($rateSet as $rate)
        {
            $this->add($rate);
        }
    }
    
	/**
	 * Required definition of interface IteratorAggregate
	 *
	 * @return Iterator
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->rates);
	}    
	
	public function toArray()
	{
        $result = array();
        foreach ($this->rates as $rate)
        {
            $result[] = $rate->toArray();
        }
        
        return $result;
    }
    
    public function size()
    {
        return count($this->rates);
    }
}

?>