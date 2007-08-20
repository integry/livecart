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
	
	protected $type;
	
    const TYPE_SALE = 0;
    const TYPE_AUTH = 1;
    const TYPE_CAPTURE = 2;
    const TYPE_VOID = 3;	
    const TYPE_REFUND = 4;
    
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
	
    public function setTransactionType($type)
    {
        $this->type = $type;
    }
    
    public function getTransactionType()
    {
        return $this->type;
    }

    public function isCaptured()
    {
        return (self::TYPE_SALE == $this->type) || (self::TYPE_CAPTURE == $this->type);
    }    
    
    public function getDetails()
    {
        return $this->rawResponse;
    }
}

?>