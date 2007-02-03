<?php

ClassLoader::import("application.model.ActiveRecordModel");

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
	
	public function getNewInstance(Product $product, Currency $currency)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->product->set($product);
		$instance->currency->set($currency);	
		$instance->reCalculatePrice();
	}
	
	public function reCalculatePrice()
	{
		$defaultCurrency = Store::getInstance()->getDefaultCurrency();
		$basePrice = $this->product->getPrice($defaultCurrency->getID());
		
		if ($this->currency->get()->rate->get())
		{
			$price = $basePrice / $this->currency->get()->rate->get();
		}
		else
		{
			$price = 0;	
		}

		return $price;
	}
}

?>