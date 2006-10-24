<?php

ClassLoader::import("application.model.ActiveRecordModel");

/**
 * Catalog translation data
 *
 * @package application.model.product
 */
class CatalogLangData extends ActiveRecordModel
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("CatalogLangData");

		$schema->registerField(new ARPrimaryForeignKeyField("catalogID", "Catalog", "ID", null, Integer::instance()));
		$schema->registerField(new ARPrimaryForeignKeyField("languageID", "Language", "ID", null, Char::instance(2)));
		$schema->registerField(new ARField("name", Varchar::instance(100)));
		$schema->registerField(new ARField("description", Varchar::instance(1024)));
	}
}

?>