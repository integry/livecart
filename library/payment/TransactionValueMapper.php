<?php

class TransactionValueMapper
{
	protected $value = null;
	
	public function get()
	{
		return $this->value;	
	}
	
	public function set($value)
	{
		$this->value = $value;
	}	
}

?>