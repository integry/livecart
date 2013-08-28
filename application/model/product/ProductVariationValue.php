<?php

ClassLoader::import('application.model.ActiveRecordModel');
ClassLoader::import('application.model.product.Product');
ClassLoader::import('application.model.product.ProductVariation');

/**
 * Relates a product variation to particular product.
 *
 * @package application.model.product
 * @author Integry Systems <http://integry.com>
 */
class ProductVariationValue extends ActiveRecordModel
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);


		public $ID;
		public $productID", "Product", "ID", null, ARInteger::instance()));
		public $variationID", "ProductVariation", "ID", null, ARInteger::instance()));
	}

	public static function getNewInstance(Product $product, ProductVariation $variation)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->product = $product);
		$instance->variation = $variation);
		return $instance;
	}
}

?>