<?php

ClassLoader::import("application.model.ActiveRecordModel");

/**
 * Product translation data
 *
 * @package application.model.product
 */
class FilterLangData extends ActiveRecordModel
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("FilterLangData");

		$schema->registerField(new ARPrimaryForeignKeyField("filterID", "Filter", "ID", null, Integer::instance()));
		$schema->registerField(new ARPrimaryForeignKeyField("languageID", "Language", "ID", null, Char::instance(2)));
		$schema->registerField(new ARField("name", Varchar::instance(40)));
	}
}

?>
