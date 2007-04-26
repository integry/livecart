<?php

class TransactionResult
{
	public $gatewayTransactionID;
	
	public $amount;
	
	public $currency;
	
	public $AVSaddr;

	public $AVSzip;
	
	public $CVVmatch;
	
	public $rawResponse;
	
	protected $isCaptured;
    
    public function __construct()
	{		
		$this->gatewayTransactionID = new TransactionValueMapper();
		$this->amount = new TransactionValueMapper();
		$this->currency = new TransactionValueMapper();
		$this->AVSaddr = new TransactionValueMapper();
		$this->AVSzip = new TransactionValueMapper();
		$this->CVVmatch = new TransactionValueMapper();
		$this->rawResponse = new TransactionValueMapper();
	}
	
	public function setAsCaptured()
	{
        $this->isCaptured = true;    
    }
    
    public function isCaptured()
    {
        return $this->isCaptured;
    }
}

?>