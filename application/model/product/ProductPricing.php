<?php

ClassLoader::import('application.model.product.ProductPrice');
ClassLoader::import('application.model.Currency');

/**
 * Product pricing logic. Allows to modify product prices and calculates prices for other currencies
 * if a price is not defined for a particular currency. This class usually should not be used directly
 * as the Product class provides most of the methods necessary for pricing manipulation.
 *
 * @package application.model.product
 * @author Integry Systems <http://integry.com>   
 */
class ProductPricing
{
	const CALCULATED = 'calculated';
	const DEFINED = 'defined';
	const BOTH = 'both';
    
    private $product;

	private $prices = array();

	private $removedPrices = array();

	public function __construct(Product $product, $prices = null)
	{
		$this->product = $product;
		        
        if (is_null($prices))
		{
            $prices = $product->getRelatedRecordSet("ProductPrice", new ARSelectFilter());            
        }

        if ($prices instanceof ARSet)
		{
			foreach ($prices as $price)
			{				
				$this->setPrice($price);
			}
		}
		else
		{
			foreach ($prices as $id => $price)
			{
				$this->prices[$id] = ProductPrice::getNewInstance($product, Currency::getInstanceById($id));
				$this->prices[$id]->price->set($price);
				$this->prices[$id]->resetModifiedStatus();
			}
		}            
	}

	public function setPrice(ProductPrice $price)
	{
		$this->prices[$price->currency->get()->getID()] = $price;
	}

	/**
	 * Get price
	 *
	 * @param Currency $currency
	 * @return ProductPrice
	 */
	public function getPrice(Currency $currency)
	{
		if (!$this->isPriceSet($currency))
		{
			$inst = ProductPrice::getNewInstance($this->product, $currency);
			$this->prices[$currency->getID()] = $inst;
		}

		return $this->prices[$currency->getID()];
	}

	/**
	 * Get price by currency code
	 *
	 * @param string $currencyCode
	 * @return ProductPrice
	 */
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
		if (isset($this->prices[$currencyCode])) 
		{
			$this->removedPrices[] = $this->prices[$currencyCode];	
		}
		
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

	/**
	 * Convert current product prices to array
	 * 
	 * @param string $part Which part of array prices you want to get ('defined', 'calculated' or 'both')
	 */
	public function toArray($part = null)
	{
		if(!in_array($part, array('defined', 'calculated', 'both'))) $part = 'both';
	    
	    $defined = array();	
		foreach ($this->prices as $inst)
		{
		    $defined[$inst->currency->get()->getID()] = $inst->price->get();
		}

		$baseCurrency = Store::getInstance()->getDefaultCurrencyCode();				
		$basePrice = isset($defined[$baseCurrency]) ? $defined[$baseCurrency] : 0;
		
		$formattedPrice = $calculated = array();

		foreach (Store::getInstance()->getCurrencySet() as $id => $currency)
		{
			if (!empty($defined[$id]))
		  	{
		  	    $calculated[$id] = $defined[$id];
			}
			else
			{
			    $calculated[$id] = ProductPrice::calculatePrice($this->product, $currency, $basePrice);
			}
        
			$formattedPrice[$id] = $currency->getFormattedPrice($calculated[$id]);		
		}
	
		$return = array('defined' => $defined, 'calculated' => $calculated, 'formattedPrice' => $formattedPrice);
		return ('both' == $part) ? $return : $return[$part];
	}
}

?>