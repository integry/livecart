<?php

ClassLoader::import("application.model.product.ProductLangData");

/**
 *
 * @package application.model.product
 */
class ProductImage extends MultilingualDataObject
{

	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("ProductImage");

		$schema->registerField(new ARPrimaryKeyField("ID", Integer::instance()));
		$schema->registerField(new ARForeignKeyField("productID", "Product", "ID", "Product", Integer::instance()));
		$schema->registerField(new ARField("position", Varchar::instance(256)));
	}
}

?>
