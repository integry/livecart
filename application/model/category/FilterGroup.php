<?php

ClassLoader::import("application.model.system.MultilingualObject");

/**
 * Filter group model
 *
 * @package application.model.product
 */
class FilterGroup extends MultilingualObject
{

	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("FilterGroup");

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("catalogID", "Catalog", "ID", "Catalog", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("specFieldID", "SpecField", "ID", "SpecField", ARInteger::instance()));
		
		$schema->registerField(new ARField("name", ARArray::instance()));
		$schema->registerField(new ARField("position", ARInteger::instance()));
		$schema->registerField(new ARField("isEnabled", ARInteger::instance(1)));
	}

	public function addFilter(Filter $filter)
	{
		$filter->filterGroup->set($this);
		$filter->save();
	}

	public static function getNewInstance()
	{
		return parent::getNewInstance(__CLASS__);
	}

	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false)
	{
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords);
	}
}

?>
