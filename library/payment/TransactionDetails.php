<?php

include_once('TransactionValueMapper.php');

/**
 *
 * @package library.payment
 * @author Integry Systems
 */
class TransactionDetails
{
	protected $recurringItems = array();

	protected $lineItems = array();

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

		'recurringItemCount' => 0
	);

	public function __construct()
	{
		foreach ($this->data as $key => $value)
		{
			$this->data[$key] = new TransactionValueMapper();
			$this->$key = $this->data[$key];
		}
	}

	public function get($key)
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

	public function addLineItem($name, $itemPrice, $quantity, $sku, $recurringItem = null)
	{
		$a = array('name' => $name, 'price' => $itemPrice, 'quantity' => $quantity, 'sku' => $sku);
		if ($recurringItem)
		{
			$this->recurringItems[] = $recurringItem;
			$this->recurringItemCount->set(count($this->recurringItems));
		}
		$this->lineItems[] = $a;
	}


	public function getRecurringItems()
	{
		return $this->recurringItems;
	}
	
	public function getLineItems()
	{
		return $this->lineItems;
	}
}

?>