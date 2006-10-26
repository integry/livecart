<?php

ClassLoader::import("application.model.ActiveRecordModel");

/**
 * Catalog translation data
 *
 * @package application.model.product
 */
class CategoryLangData extends ActiveRecordModel
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("CategoryLangData");

		$schema->registerField(new ARPrimaryForeignKeyField("categoryID", "Category", "ID", null, Integer::instance()));
		$schema->registerField(new ARPrimaryForeignKeyField("languageID", "Language", "ID", null, Char::instance(2)));
		$schema->registerField(new ARField("name", Varchar::instance(100)));
		$schema->registerField(new ARField("description", Varchar::instance(1024)));
	}
}

?>