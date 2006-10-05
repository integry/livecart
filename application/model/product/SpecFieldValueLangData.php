<?php


ClassLoader::import("application.model.ActiveRecordModel");

/**
 * Filter group translation data
 * 
 * @package application.model.product
 */
class SpecFieldValueLangData extends ActiveRecordModel {
	
	public static function defineSchema($className = __CLASS__) {
		
		$schema = self::getSchemaInstance($className);
		$schema->setName("SpecFieldValueLangData");
		
		$schema->registerField(new ARPrimaryForeignKeyField("specFieldValueID", "SpecFieldValue", "ID", null, Integer::instance()));
		$schema->registerField(new ARPrimaryForeignKeyField("languageID", "Language", "ID", null, Char::instance(2)));
		$schema->registerField(new ARField("value", Varchar::instance(200)));
	}
}

?>