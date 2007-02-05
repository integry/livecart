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

	public static function getRecordSet($specFieldId)
	{
        $filter = new ARSelectFilter();
		$filter->setOrder(new ARFieldHandle(__CLASS__, "position"));
        $filter->setCondition(new EqualsCond(new ARFieldHandle(__CLASS__, 'specFieldID'), $specFieldId));

        return parent::getRecordSet(__CLASS__, $filter, false);
	}

	public static function getRecordSetArray($specFieldId)
	{
        $filter = new ARSelectFilter();
		$filter->setOrder(new ARFieldHandle(__CLASS__, "position"));
        $filter->setCondition(new EqualsCond(new ARFieldHandle(__CLASS__, 'specFieldID'), $specFieldId));

        return parent::getRecordSetArray(__CLASS__, $filter, false);
	}

	/**
	 *  Get blank active record instance
	 *
	 *	@param SpecField $field Instance of SpecField (must be a selector field)
	 *  @return SpecFieldValue
	 */
	public static function getNewInstance(SpecField $field)
	{
	    if (!in_array($field->type->get(), array(SpecField::TYPE_NUMBERS_SELECTOR, SpecField::TYPE_TEXT_SELECTOR)))
	    {
		  	throw new Exception('Cannot create a SpecFieldValue for non-selector field!');
		}
		
		$instance = parent::getNewInstance(__CLASS__);
		$instance->specField->set($field);
		return $instance;
	}

	/**
	 * Get active record instance
	 *
	 * @param unknown_type $recordID
	 * @param unknown_type $loadRecordData
	 * @param unknown_type $loadReferencedRecords
	 * @return SpecFieldValue
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
	 * Delete value from database
	 */
	public static function deleteById($id)
	{
	    parent::deleteByID(__CLASS__, (int)$id);
	}
	
	public function getFormFieldName()
	{
	  	return 'specItem_' . $this->getID();
	}

	protected function insert()
	{
	   	// get current max position
		if (!$this->position->get())
		{		  
			$filter = new ARSelectFilter();
		   	$cond = new EqualsCond(new ARFieldHandle('SpecFieldValue', 'specFieldID'), $this->specField->get()->getID());
		   	$filter->setCondition($cond);
			$filter->setOrder(new ARFieldHandle('SpecFieldValue', 'position'), 'DESC');
		   	$filter->setLimit(1);
		   	$res = ActiveRecordModel::getRecordSet('SpecFieldValue', $filter);
		   	if ($res->size() > 0)
		   	{
			 	$item = $res->get(0);
				$pos = $item->position->get() + 1;
			}
			else
			{
				$pos = 0;  	
			}
			
			$this->position->set($pos);
		}
			
		return parent::insert();		
	}	
}

?>