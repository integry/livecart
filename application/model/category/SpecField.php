<?php

ClassLoader::import("application.model.MultilingualDataObject");
ClassLoader::import("application.model.product.SpecFieldLangData");
ClassLoader::import("application.model.product.Catalog");

/**
 * Specification field class
 *
 * @package application.model.product
 */
class SpecField extends MultilingualDataObject
{

	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("SpecField");

		$schema->registerField(new ARPrimaryKeyField("ID", Integer::instance()));
		$schema->registerField(new ARForeignKeyField("catalogID", "Catalog", "ID", "Catalog", Integer::instance()));
		$schema->registerField(new ARField("type", Integer::instance(2)));
		$schema->registerField(new ARField("dataType", Integer::instance(2)));
		$schema->registerField(new ARField("position", Integer::instance(2)));
		$schema->registerField(new ARField("handle", Varchar::instance(40)));
	}

	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false)
	{
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords);
	}

	public static function getNewInstance()
	{
		return parent::getNewInstance(__CLASS__);
	}

	public static function getRecordSet(ARSelectFilter $filter, $loadReferencedRecords = false)
	{
		return parent::getRecordSet(__CLASS__, $filter, $loadReferencedRecords);
	}

	public static function getRecordSetArray(ARSelectFilter $filter, $loadReferencedRecords = false)
	{
		return parent::getRecordSetArray(__CLASS__, $filter, $loadReferencedRecords);
	}
}

?>