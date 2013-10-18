<?php


/**
 * Defines a product variation (parameter) type.
 *
 * This can be "size", "color", "weight", etc.
 *
 * @package application/model/product
 * @author Integry Systems <http://integry.com>
 */
class ProductVariationType extends MultilingualObject
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);


		public $ID;
		public $productID", "Product", "ID", null, ARInteger::instance()));

		public $name;
		public $position;
	}

	public static function getNewInstance(Product $product)
	{
		$instance = new self();
		$instance->product = $product;
		return $instance;
	}

	public function beforeCreate()
	{
		if (is_null($this->position))
		{
			$this->setLastPosition('product');
		}

		parent::insert();
	}
}

?>