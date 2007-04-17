<?php

include_once('ShippingResultInterface.php');

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
}

?>