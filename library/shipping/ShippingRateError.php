<?php

include_once('ShippingResultInterface.php');

/**
 *
 * @package library.shipping
 * @author Integry Systems 
 */
class ShippingRateError implements ShippingResultInterface
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