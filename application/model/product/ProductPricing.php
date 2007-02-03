<?php

/**
 *	A container class containing product prices in all currencies
 */
class ProductPricing
{
	private $product;
	
	private $prices = array();
	
	private $removedPrices = array();
	
	public function __construct(Product $product, $prices = array())
	{
		$this->product = $product;
		
		foreach ($prices as $id => $price)
		{
			$inst = ProductPrice::getNewInstance($product, Currency::getInstanceById($id));
			$inst->markAsLoaded();
		}
	}
	
	public function setPrice(ProductPrice $price)
	{
		$this->prices[$price->currency->get()->getID()] = $price;
	}
	
	public function getPrice(Currency $currency)
	{
		if ($this->isPriceSet($currency))
		{
			return $this->prices[$currency->getID()];
		}
		else
		{
			$inst = ProductPrice::getNewInstance($this->product, $currency);			
			$inst->price->set($inst->reCalculatePrice());		

			return $inst;
		}
	}
	
	public function removePrice(Currency $currency)
	{
		$this->removedPrices[$currency->getID()] = $currency;
		unset($this->prices[$currency->getID()]);
	}
	
	public function isPriceSet(Currency $currency)
	{
		return isset($this->prices[$currency->getID()]);	
	}
	
	public function save()
	{
		foreach ($this->prices as $price)
		{
			$price->save();
		}
	
		foreach ($this->removedPrices as $price)
		{
			$price->delete();
		}
		$this->removedPrices = array();
	}
	
	public function toArray()
	{
		$calculated = array();
		$defined = array();
		
		foreach ($this->prices as $inst)
		{
			$price = $inst->price->get();

			$defined[$inst->getID()] = $price;
		
			if (is_null($price))
			{
				$price = $inst->reCalculatePrice();
			}

			$calculated[$inst->getID()] = $price;
		}
	
		return array('defined' => $defined,
					 'calculated' => $calculated);
	}
}

?>