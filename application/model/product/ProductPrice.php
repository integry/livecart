<?php

ClassLoader::import("application.model.ActiveRecordModel");

/**
 * Product prices data
 *
 * @package application.model.product
 */
class ProductPrice extends ActiveRecordModel
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("ProductPrice");

		$schema->registerField(new ARPrimaryForeignKeyField("productID", "Product", "ID", null, Integer::instance()));
		$schema->registerField(new ARPrimaryForeignKeyField("currencyID", "Currency", "ID", null, Char::instance(3)));
		$schema->registerField(new ARField("price", Float::instance(16)));
	}
}

?>
