<?php

class GoogleCheckoutRequest
{
	private $data;

	public function __construct()
	{
		$this->data = new SimpleXMLElement('<shopping-cart></shopping-cart>');
	}

	public function addItem(OrderedItem $item)
	{
		if (!$this->data->xpath('/items'))
		{
			$this->data->addChild('items');
		}
	}

	public function addExcludedCountry()
	{

	}

	public function getXML()
	{

	}
}

?>