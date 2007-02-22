<?php

ClassLoader::import("application.model.ActiveRecordModel");
ClassLoader::import("application.model.Currency");

/**
 * Product price class
 * Prices can be entered in different currencies
 *
 * @package application.model.product
 */
class ProductPrice extends ActiveRecordModel
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("ProductPrice");

		$schema->registerField(new ARPrimaryForeignKeyField("productID", "Product", "ID", null, ARInteger::instance()));
		$schema->registerField(new ARPrimaryForeignKeyField("currencyID", "Currency", "ID", null, ARChar::instance(3)));
		$schema->registerField(new ARField("price", ARFloat::instance(16)));
	}
	
	public static function getNewInstance(Product $product, Currency $currency)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->product->set($product);
		$instance->currency->set($currency);	
		
		return $instance;
	}

	/**
	 * Loads a set of active record product price by using a filter
	 *
	 * @param ARSelectFilter $filter
	 * @param bool $loadReferencedRecords
	 *
	 * @return ARSet
	 */
	public static function getRecordSet(ARSelectFilter $filter, $loadReferencedRecords = false)
	{
		return parent::getRecordSet(__CLASS__, $filter, $loadReferencedRecords);
	}
	
	/**
	 * Get record set of product prices
	 *
	 * @param Product $product
	 * 
	 * @return ARSet
	 */
	public static function getProductPricesSet(Product $product)
	{
	    $filter = self::getProductPricesFilter($product);
	    return self::getRecordSet($filter);
	}
	
	/**
	 * Get product prices filter
	 *
	 * @param Product $product
	 * 
	 * @return ARSelectFilter
	 */
	private static function getProductPricesFilter(Product $product)
	{
	    ClassLoader::import("application.model.Currency");
	    
	    $filter = new ARSelectFilter();
	    $filter->setCondition(new EqualsCond(new ARFieldHandle(__CLASS__, 'productID'), $product->getID()));
	    
	    return $filter;
	}
	
	public static function getInstance(Product $product, Currency $currency)
	{
		$filter = new ARSelectFilter();
		$cond = new EqualsCond(new ARFieldHandle('ProductPrice', 'productID'), $product->getID());
		$cond->addAND(new EqualsCond(new ARFieldHandle('ProductPrice', 'currencyID'), $currency->getID()));
		$filter->setCondition($cond);

		$set = parent::getRecordSet('ProductPrice', $filter);
		
		if ($set->size() > 0)
		{
		  	$instance = $set->get(0);
		}
		else
		{
		  	$instance = self::getNewInstance($product, $currency);
		}
			
		return $instance;
	}

	public function reCalculatePrice()
	{
		$defaultCurrency = Store::getInstance()->getDefaultCurrency();
		$basePrice = $this->product->get()->getPrice($defaultCurrency->getID(), Product::DO_NOT_RECALCULATE_PRICE);
		
		if ($this->currency->get()->rate->get())
		{
			$price = $basePrice / $this->currency->get()->rate->get();
		}
		else
		{
			$price = 0;	
		}

		return round($price, 2);
	}
}

?>