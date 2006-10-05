<?php

ClassLoader::import("application.model.ActiveRecordModel");

/**
 * ProductImage translation data
 * 
 * @package application.model.product
 */
class ProductImageLangData extends ActiveRecordModel {
	
	public static function defineSchema($className = __CLASS__) {
		
		$schema = self::getSchemaInstance($className);
		$schema->setName("ProductImageLangData");
		
		$schema->registerField(new ARPrimaryForeignKeyField("productImageID", "ProductImage", "ID", null, Integer::instance()));
		$schema->registerField(new ARPrimaryForeignKeyField("languageID", "Language", "ID", null, Char::instance(2)));
		$schema->registerField(new ARField("title", Varchar::instance(100)));		
	}
}

?>