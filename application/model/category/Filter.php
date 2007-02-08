<?php

ClassLoader::import("application.model.system.MultilingualObject");
ClassLoader::import('application.model.category.SpecFieldValue');
ClassLoader::import('application.model.category.FilterGroup');

/**
 *
 * @package application.model.category
 */
class Filter extends MultilingualObject
{
    /**
     * Define filter schema
     */
	public static function defineSchema()
	{
		$schema = self::getSchemaInstance(__CLASS__);
		$schema->setName(__CLASS__);

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("filterGroupID", "FilterGroup", "ID", "FilterGroup", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("specFieldValueID", "SpecFieldValue", "ID", "SpecFieldValue", ARInteger::instance()));
		$schema->registerField(new ARField("name", ARArray::instance()));
		$schema->registerField(new ARField("position", ARInteger::instance(2)));
		$schema->registerField(new ARField("type", ARInteger::instance(2)));
		$schema->registerField(new ARField("rangeStart", ARFloat::instance(40)));
		$schema->registerField(new ARField("rangeEnd", ARFloat::instance(40)));
		$schema->registerField(new ARField("rangeDateStart", ARDate::instance()));
		$schema->registerField(new ARField("rangeDateEnd", ARDate::instance()));
		$schema->registerField(new ARField("handle", ARVarchar::instance(40)));
	}

	/**
	 * Get filter active record instance
	 *
	 * @param integer $recordID
	 * @param boolean $loadRecordData
	 * @param boolean $loadReferencedRecords
	 * @return Filter
	 */
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false)
	{
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords);
	}

	/**
	 * Get new instance of Filter active record
	 *
	 * @return Filter
	 */
	public static function getNewInstance()
	{
		return parent::getNewInstance(__CLASS__);
	}

	/**
	 * Get record set of filters using select filter 
	 *
	 * @param ARSelectFilter $filter
	 * @return ARSet
	 */
	public static function getRecordSetArray(ARSelectFilter $filter, $loadReferencedRecords = false)
	{
	    return parent::getRecordSetArray(__CLASS__, $filter, $loadReferencedRecords);
	}

	/**
	 * Get record set as array of filters using select filter 
	 *
	 * @param ARSelectFilter $filter
	 * @return array
	 */
	public static function getRecordSet(ARSelectFilter $filter, $loadReferencedRecords = false)
	{
		return parent::getRecordSet(__CLASS__, $filter, $loadReferencedRecords);
	}


	/**
	 * Delete spec field from database
	 */
	public static function deleteByID($id)
	{
	    parent::deleteByID(__CLASS__, (int)$id);
	}

}

?>