<?php

ClassLoader::import("application.model.ActiveRecordModel");

/**
 *
 * @package application.model.product
 */
class Discount extends ActiveRecordModel
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("Discount");

		$schema->registerField(new ARPrimaryKeyField("ID", Integer::instance()));
		$schema->registerField(new ARForeignKeyField("productID", "Product", "ID", null, Integer::instance()));

		$schema->registerField(new ARField("amount", Integer::instance()));
		$schema->registerField(new ARField("discountType", Integer::instance()));
		$schema->registerField(new ARField("discountValue", Float::instance(16)));
	}
}

?>
