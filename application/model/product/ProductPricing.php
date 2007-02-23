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
	
	public static function addShippingValidator(RequestValidator $validator)
	{
		// shipping related numeric field validations
		$validator->addCheck('shippingSurcharge', new IsNumericCheck('_err_surcharge_not_numeric'));		  
		$validator->addFilter('shippingSurcharge', new NumericFilter());		    
						
		$validator->addCheck('minimumQuantity', new IsNumericCheck('_err_quantity_not_numeric'));		  
		$validator->addCheck('minimumQuantity', new MinValueCheck('_err_quantity_negative', 0));	
		$validator->addFilter('minimumQuantity', new NumericFilter());		    

		$validator->addFilter('shippingHiUnit', new NumericFilter());		    
		$validator->addCheck('shippingHiUnit', new IsNumericCheck('_err_weight_not_numeric'));		  
		$validator->addCheck('shippingHiUnit', new MinValueCheck('_err_weight_negative', 0));	

		$validator->addFilter('shippingLoUnit', new NumericFilter());				
		$validator->addCheck('shippingLoUnit', new IsNumericCheck('_err_weight_not_numeric'));		  
		$validator->addCheck('shippingLoUnit', new MinValueCheck('_err_weight_negative', 0));

		return $validator;
	}
	
	public static function addPricesValidator(RequestValidator $validator)
	{
		// price in base currency
		$baseCurrency = Store::getInstance()->getDefaultCurrency()->getID();
		$validator->addCheck('price_' . $baseCurrency, new IsNotEmptyCheck('_err_price_empty'));			    
	    
	    $currencies = Store::getInstance()->getCurrencyArray();
		foreach ($currencies as $currency)
		{
			$validator->addCheck('price_' . $currency, new IsNumericCheck('_err_price_invalid'));		  		  	
			$validator->addCheck('price_' . $currency, new MinValueCheck('_err_price_negative', 0));		  
			$validator->addFilter('price_' . $currency, new NumericFilter());		    
		}
		
		return $validator;
	}
}

?>