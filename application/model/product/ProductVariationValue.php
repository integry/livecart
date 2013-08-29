<?php


/**
 * Relates a product variation to particular product.
 *
 * @package application/model/product
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
		$instance = new __CLASS__();
		$instance->product = $product;
		$instance->variation = $variation;
		return $instance;
	}
}

?>