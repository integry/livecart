<?php

include_once('TransactionValueMapper.php');

class TransactionDetails
{
	protected $data = array(
		
		// billing address data
		'firstName' => null,
		'lastName' => null,
		'companyName' => null,
		
		'address' => null,
		'city' => null,
		'state' => null,
		'country' => null,	
		'postalCode' => null,
	
		'phone' => null,
		'email' => null,	
		
		// shipping address data
		'shippingFirstName' => null,
		'shippingLastName' => null,
		'shippingCompanyName' => null,
		
		'shippingAddress' => null,
		'shippingCity' => null,
		'shippingState' => null,
		'shippingCountry' => null,	
		'shippingPostalCode' => null,
	
		'shippingPhone' => null,
		'shippingEmail' => null,	
		
		// customer data
		'clientID' => null,
		'ipAddress' => null,	
		
		// merchant transaction data
		'invoiceID' => null,	
		
		// transaction data
		'isCompleted' => null,
		'amount' => null,
		'currency' => null,
		'description' => null,

		'gatewayTransactionID' => null,
	
	);
	
	public function __construct()
	{
		foreach ($this->data as $key => $value)
		{
			$this->data[$key] = new TransactionValueMapper();
			$this->$key = $this->data[$key];
		}
	}
	
	public function getValue($key)
	{
		if (isset($this->data[$key]))
		{
			return $this->data[$key]->get();
		}
	}
	
	public function getData()
	{
        return $this->data;
    }
    
    public function getName()
    {
        return $this->firstName->get() . ' ' . $this->lastName->get();
    }
}

?>