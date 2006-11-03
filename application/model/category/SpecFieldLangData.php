<?php

ClassLoader::import("application.model.ActiveRecordModel");

/**
 * Specification field translation data
 *
 * @package application.model.product
 */
class SpecFieldLangData extends ActiveRecordModel
{

	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("SpecFieldLangData");

		$schema->registerField(new ARPrimaryForeignKeyField("specFieldID", "SpecField", "ID", null, Integer::instance()));
		$schema->registerField(new ARPrimaryForeignKeyField("languageID", "Language", "ID", null, Char::instance(2)));
		$schema->registerField(new ARField("name", Varchar::instance(40)));
		$schema->registerField(new ARField("description", Varchar::instance(1024)));
	}
}

?>
