<?php

ClassLoader::import("application.model.system.MultilingualObject");

/**
 * Specification field value class
 *
 * @package application.model.category
 */
class SpecFieldValue extends MultilingualObject
{

	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("SpecFieldValue");

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("specFieldID", "SpecField", "ID", "SpecField", ARInteger::instance()));
		$schema->registerField(new ARField("position", ARInteger::instance(2)));

		$schema->registerField(new ARField("value", ARArray::instance()));
		$schema->registerField(new ARField("position", ARInteger::instance(2)));

	}

	public static function getRecordSetArray($specFieldId)
	{
        $filter = new ARSelectFilter();
		$filter->setOrder(new ARFieldHandle(__CLASS__, "position"));
        $filter->setCondition(new EqualsCond(new ARFieldHandle(__CLASS__, 'specFieldID'), $specFieldId));

        return parent::getRecordSetArray(__CLASS__, $filter, false);
	}

	/**
	 * Get blank active record instance
	 *
	 * @param unknown_type $recordID
	 * @param unknown_type $loadRecordData
	 * @param unknown_type $loadReferencedRecords
	 * @return ActiveRecord
	 */
	public static function getNewInstance()
	{
	    return parent::getNewInstance(__CLASS__);
	}

	/**
	 * Get cctive record instance
	 *
	 * @param unknown_type $recordID
	 * @param unknown_type $loadRecordData
	 * @param unknown_type $loadReferencedRecords
	 * @return ActiveRecord
	 */
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false)
	{
	    return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords);
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
	public static function delete($id)
	{
	    parent::deleteByID(__CLASS__, (int)$id);
	}
}

?>