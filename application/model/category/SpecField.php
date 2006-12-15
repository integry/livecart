<?php

ClassLoader::import("application.model.system.MultilingualObject");
//ClassLoader::import("application.model.category.SpecFieldLangData");
ClassLoader::import("application.model.category.Category");

/**
 * Specification field class
 *
 * @package application.model.product
 */
class SpecField extends MultilingualObject
{

	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("SpecField");


		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("categoryID", "Category", "ID", "Category", ARInteger::instance()));

		$schema->registerField(new ARField("categoryID", ARInteger::instance()));
		$schema->registerField(new ARField("name", ARArray::instance()));
		$schema->registerField(new ARField("description", ARArray::instance()));
		$schema->registerField(new ARField("type", ARInteger::instance(2)));
		$schema->registerField(new ARField("dataType", ARInteger::instance(2)));
		$schema->registerField(new ARField("position", ARInteger::instance(2)));
		$schema->registerField(new ARField("handle", ARVarchar::instance(40)));
	}

	/**
	 * Get instance SpecField record by id
	 *
	 * @param mixred $recordID Id
	 * @param bool $loadRecordData If true load data
	 * @param bool $loadReferencedRecords If true load references. And $loadRecordData is true load a data also
	 *
	 * @return  SpecField
	 */
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false)
	{
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords);
	}

	/**
	 * Get blank SpecField record
	 *
	 * @return  SpecField
	 */
	public static function getNewInstance()
	{
		return parent::getNewInstance(__CLASS__);
	}

	/**
	 * Get a set of SpecField records
	 *
	 * @param ARSelectFilter $filter
	 * @param bool $loadReferencedRecords Load referenced tables data
	 *
	 * @return ActiveRecordSet
	 */
	public static function getRecordSet(ARSelectFilter $filter, $loadReferencedRecords = false)
	{
		return parent::getRecordSet(__CLASS__, $filter, $loadReferencedRecords);
	}

	/**
	 * Get a set of SpecField records
	 *
	 * @param ARSelectFilter $filter
	 * @param bool $loadReferencedRecords Load referenced tables data
	 *
	 * @return array
	 */
	public static function getRecordSetArray(ARSelectFilter $filter, $loadReferencedRecords = false)
	{
		return parent::getRecordSetArray(__CLASS__, $filter, $loadReferencedRecords);
	}

    /**
     * Set a whole language field at a time. You can allways skip some language, bat as long as it occurs in
     * languages array it will be writen into the database as empty string. I spent 2 hours writing this feature =]
     *
     * @example $specField->setLanguageField('name', array('en' => 'Name', 'lt' => 'Vardas', 'de' => 'Name'), array('lt', 'en', 'de'))
     *
     * @param string $fieldName Field name in database schema
     * @param array $fieldValue Field value in different languages
     * @param array $langCodeArray Language codes
     */
	public function setLanguageField($fieldName, $fieldValue, $langCodeArray)
	{
	    foreach ($langCodeArray as $lang)
	    {
	        $this->setValueByLang($fieldName, $lang, isset($fieldValue[$lang]) ? $fieldValue[$lang] : '');
	    }
	}

	public function getFiltersGroupsList()
	{
		$filter = new ARSelectFilter();
		$filter->setOrder(new ARFieldHandle("FilterGroup", "position"));
		$filter->setCondition(new EqualsCond(new ARFieldHandle("FilterGroup", "specFieldID"), $this->getID()));

		return FilterGroup::getRecordSetArray($filter);
	}


	/**
	 * This method is checking if SpecField record with passed id exist in the database
	 *
	 * @param int $id Record id
	 * @return bool
	 */
	public static function exists($id)
	{
	    return ActiveRecord::objectExists(__CLASS__, (int)$id);
	}

	/**
	 * Delete spec field from database
	 */
	public static function delete($id)
	{
	    parent::deleteByID(__CLASS__, (int)$id);
	}

	/**
	 * Loads a set of spec field records in current category
	 *
	 * @return ARSet
	 */
	public function getValuesList()
	{
		return SpecFieldValue::getRecordSetArray($this->getID());
	}
	
	public static function getSelectorValueTypes()
	{
	    return array (1, 5);
	}
}

?>