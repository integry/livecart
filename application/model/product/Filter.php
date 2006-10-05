<?php

ClassLoader::import("application.model.MultilingualDataObject");
ClassLoader::import("application.model.product.FilterLangData");

class Filter extends MultilingualDataObject {
	
	public static function defineSchema($className = __CLASS__) {
		
		$schema = self::getSchemaInstance($className);
		$schema->setName("Filter");
		
		$schema->registerField(new ARPrimaryKeyField("ID", Integer::instance()));
		$schema->registerField(new ARForeignKeyField("filterGroupID", "FilterGroup", "ID", "FilterGroup", Integer::instance()));
		
		$schema->registerField(new ARField("position", Integer::instance(2)));
		$schema->registerField(new ARField("type", Integer::instance(2)));
		
		$schema->registerField(new ARField("rangeStart", Float::instance(2)));
		$schema->registerField(new ARField("rangeEnd", Float::instance(40)));
	}
	
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false) {
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords);
	}
	
	public static function getNewInstance() {
		return parent::getNewInstance(__CLASS__);
	}
}

?>