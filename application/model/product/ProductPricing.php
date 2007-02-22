<?php

ClassLoader::import('application.model.product.ProductPrice');
ClassLoader::import('application.model.Currency');

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

		if ($prices instanceof ARSet)
		{
			foreach ($prices as $price)
			{
				$this->prices[$price->currency->get()->getID()] = $price;
			}		   
		}		
		else
		{
			foreach ($prices as $id => $price)
			{
				$this->prices[$id] = ProductPrice::getNewInstance($product, Currency::getInstanceById($id));
				$this->prices[$id]->price->set($price);
				$this->prices[$id]->markAsLoaded();			
			}		  
		}
	}
	
	public function setPrice(ProductPrice $price)
	{
		$this->prices[$price->currency->get()->getID()] = $price;
	}
	
	public function getPrice(Currency $currency)
	{
		if (!$this->isPriceSet($currency))
		{
			$inst = ProductPrice::getNewInstance($this->product, $currency);			
			$this->prices[$currency->getID()] = $inst;
		}

		return $this->prices[$currency->getID()];
	}
	
	public function getPriceByCurrencyCode($currencyCode)
	{
	  	return $this->getPrice(Currency::getInstanceByID($currencyCode));
	}	
	
	public function removePrice(Currency $currency)
	{
		$this->removedPrices[$currency->getID()] = $currency;
		unset($this->prices[$currency->getID()]);
	}

	public function removePriceByCurrencyCode($currencyCode)
	{
		unset($this->prices[$currencyCode]);
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

		$defined = array();		
		foreach ($this->prices as $inst)
		{
			$defined[$inst->currency->get()->getID()] = $inst->price->get();
		}

		$calculated = array();

		foreach (Store::getInstance()->getCurrencySet() as $currency)
		{
		  	if (!empty($defined[$currency->getID()]))
		  	{
			    $calculated[$currency->getID()] = $defined[$currency->getID()];
			}
			else
			{
				$price = ProductPrice::getNewInstance($this->product, $currency);
			    $calculated[$currency->getID()] = $price->reCalculatePrice();
			}
		}
	
		return array('defined' => $defined,
					 'calculated' => $calculated);
	}
}

?>