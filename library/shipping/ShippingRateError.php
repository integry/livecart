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

	protected $errorMessage;

	public function __construct($message = null)
	{
		$this->errorMessage = (string)$message;
	}

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