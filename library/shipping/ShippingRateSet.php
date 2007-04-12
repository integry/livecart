<?php

class ShippingRateSet implements ShippingResultInterface
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
}

?>