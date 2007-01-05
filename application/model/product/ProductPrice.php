<?php

ClassLoader::import("application.model.ActiveRecordModel");

/**
 * Product price class.
 * Price can be entered in different currencies
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
}

?>