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
		$schema->setName($className);

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("productID", "Product", "ID", null, ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("variationID", "ProductVariation", "ID", null, ARInteger::instance()));
	}
}

?>