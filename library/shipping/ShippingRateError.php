<?php

class ShippingRateError
{
    protected $rawResponse;
    
    function setRawResponse($rawResponse)
    {
        $this->rawResponse = $rawResponse;
    }

    function getRawResponse()
    {
        return $this->rawResponse;
    }
}

?>