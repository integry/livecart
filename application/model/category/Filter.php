<?php

ClassLoader::import("application.model.system.MultilingualObject");

/**
 *
 * @package application.model.category
 */
class Filter extends MultilingualObject
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("Filter");

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

	}

	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false)
	{
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords);
	}

	public static function getNewInstance()
	{
		return parent::getNewInstance(__CLASS__);
	}

	public static function getRecordSetArray(ARSelectFilter $filter)
	{
	    return parent::getRecordSetArray(__CLASS__, $filter);
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

	/**
	 * Delete spec field from database
	 */
	public static function deleteByID($id)
	{
	    parent::deleteByID(__CLASS__, (int)$id);
	}
}

?>