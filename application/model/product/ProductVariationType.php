<?php

ClassLoader::import('application.model.product.Product');
ClassLoader::import('application.model.system.MultilingualObject');
ClassLoader::import('application.model.product.ProductVariationTypeSet', true);

/**
 * Defines a product variation (parameter) type.
 *
 * This can be "size", "color", "weight", etc.
 *
 * @package application.model.product
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
		$instance = parent::getNewInstance(__CLASS__);
		$instance->product = $product);
		return $instance;
	}

	protected function insert()
	{
		if (is_null($this->position->get()))
		{
			$this->setLastPosition('product');
		}

		parent::insert();
	}
}

?>