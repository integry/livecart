<?php

/**
 * 
 *
 * @package application/model/order
 * @author Integry Systems <http://integry.com> 
 */
class OrderException extends ApplicationException
{
	const MIN_QUANT = 1;
	const MAX_QUANT = 2;
	const MIN_TOTAL = 3;
	const MAX_TOTAL = 4;
 
	private $type;	   
	private $value;
	private $limit;
		 
	public function __construct($type, $value, $limit, LiveCart $application)
	{
		$this->type = $type;
		$this->value = $value;
		$this->limit = $limit; 
	}
	
	public function toArray()
	{
		return array(
		
					'type' => $this->type,
					'value' => $this->value,
					'limit' => $this->limit,
														
				);
	}
}

?>