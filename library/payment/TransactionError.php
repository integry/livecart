<?php

class TransactionError
{
	protected $message;
	protected $details;
	
	public function __construct($message, $details)
	{
		$this->message = $message;
		$this->details = $details;
	}	
	
	public function getMessage()
	{
		return $this->message;
	}
	
	public function getDetails()
	{
		return $this->details;
	}
}

?>