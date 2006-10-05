<?php

ClassLoader::import("application.model.MultilingualDataObject");
ClassLoader::import("application.model.product.FilterGroupLangData");

/**
 * Filter group model
 *
 * @package application.model.product
 */
class FilterGroup extends MultilingualDataObject {
	
	public static function defineSchema($className = __CLASS__) {
		
		$schema = self::getSchemaInstance($className);
		$schema->setName("FilterGroup");
		
		$schema->registerField(new ARPrimaryKeyField("ID", Integer::instance()));
		$schema->registerField(new ARForeignKeyField("catalogID", "Catalog", "ID", "Catalog", Integer::instance()));
		$schema->registerField(new ARForeignKeyField("specFieldID", "SpecField", "ID", "SpecField", Integer::instance()));
		$schema->registerField(new ARField("position", Integer::instance()));
		$schema->registerField(new ARField("isEnabled", Integer::instance(1)));
	}
	
	public function addFilter(Filter $filter) {
		$filter->filterGroup->set($this);
		$filter->save();
	}
	
	public static function getNewInstance() {
		return parent::getNewInstance(__CLASS__);
	}
	
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false) {
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords);
	}
}

?>