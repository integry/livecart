<?php

ClassLoader::import("application.model.ActiveRecordModel");

/**
 * Product translation data
 *
 * @package application.model.product
 */
class ProductLangData extends ActiveRecordModel
{

	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("ProductLangData");

		$schema->registerField(new ARPrimaryForeignKeyField("productID", "Product", "ID", null, Integer::instance()));
		$schema->registerField(new ARPrimaryForeignKeyField("languageID", "Language", "ID", null, Char::instance(2)));
		$schema->registerField(new ARField("name", Varchar::instance(100)));
		//$schema->registerField(new ARField("description", Varchar::instance(512)));
		$schema->registerField(new ARField("shortDescription", Varchar::instance(256)));
		$schema->registerField(new ARField("fullDescription", Varchar::instance(1024)));
	}
}

?>
