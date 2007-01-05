<?php

ClassLoader::import("application.model.system.MultilingualObject");

/**
 *
 * @package application.model.product
 */
class ProductImage extends MultilingualObject
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("ProductImage");

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("productID", "Product", "ID", "Product", ARInteger::instance()));
		$schema->registerField(new ARField("title", ARVarchar::instance(256)));
		$schema->registerField(new ARField("position", ARInteger::instance()));
	}
}

?>
