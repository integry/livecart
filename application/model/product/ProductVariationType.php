<?php

ClassLoader::import('application.model.product.Product');
ClassLoader::import('application.model.system.MultilingualObject');
ClassLoader::import('application.model.product.ProductVariationTypeSet');

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
		$schema->setName($className);

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("productID", "Product", "ID", null, ARInteger::instance()));

		$schema->registerField(new ARField("name", ARArray::instance()));
		$schema->registerField(new ARField("position", ARInteger::instance(4)));
	}

	public static function getNewInstance(Product $product)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->product->set($product);
		return $instance;
	}

	protected function insert()
	{
		$this->setLastPosition();
		parent::insert();
	}
}

?>