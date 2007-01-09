<?php

ClassLoader::import("application.model.system.MultilingualObject");

/**
 * Filter group model
 *
 * @package application.model.category
 */
class FilterGroup extends MultilingualObject
{

	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("FilterGroup");

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
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

	/**
	 * Get new instance of FilterGroup record
	 *
	 * @return ActiveRecord
	 */
	public static function getNewInstance()
	{
		return parent::getNewInstance(__CLASS__);
	}

	/**
	 * @return MultilingualObject
	 */
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false)
	{
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords);
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

	public function setLanguageField($fieldName, $fieldValue, $langCodeArray)
	{
	    foreach ($langCodeArray as $lang)
	    {
	        $this->setValueByLang($fieldName, $lang, isset($fieldValue[$lang]) ? $fieldValue[$lang] : '');
	    }
	}

	public static function getRecordSetArray(ARSelectFilter $filter)
	{
	    return parent::getRecordSetArray(__CLASS__, $filter);
	}

	public static function delete($id)
	{
	    return parent::deleteByID(__CLASS__, $id);
	}

	/**
	 * Loads a set of spec field records in current category
	 *
	 * @return ARSet
	 */
	public function getFiltersList()
	{
		$filter = new ARSelectFilter();

		$filter->setOrder(new ARFieldHandle("Filter", "position"));
		$filter->setCondition(new EqualsCond(new ARFieldHandle("Filter", "filterGroupID"), $this->getID()));

		return Filter::getRecordSetArray($filter);
	}

	/**
	 * Save group filters in database
	 *
	 * @param array $filters
	 * @param int $specFieldType 
	 * @param array $languages
	 */
    public function saveFilters($filters, $specFieldType, $languages) 
    {
        $position = 1;
        foreach ($filters as $key => $value)
        {
            if(preg_match('/^new/', $key))
            {
                $filter = Filter::getNewInstance();
                $filter->setFieldValue('position', 100000); // Now new filter will appear last in active list.
            }
            else
            {
                $filter = Filter::getInstanceByID((int)$key);
            }

            $filter->setLanguageField('name', @array_map($htmlspecialcharsUtf_8, $value['name']),  array_keys($languages));
            
            
            
            if($specFieldType == SpecField::TYPE_TEXT_DATE)
            {
                $filter->setFieldValue('rangeDateStart', $value['rangeDateStart']);
                $filter->setFieldValue('rangeDateEnd', $value['rangeDateEnd']);
                $filter->rangeStart->setNull();
                $filter->rangeEnd->setNull();
                $filter->specFieldValue->setNull();
            }
            else if(!in_array($specFieldType, SpecField::getSelectorValueTypes()))
            {
                $filter->setFieldValue('rangeStart', $value['rangeStart']);
                $filter->setFieldValue('rangeEnd', $value['rangeEnd']);
                $filter->rangeDateStart->setNull();
                $filter->rangeDateEnd->setNull();
                $filter->specFieldValue->setNull();
            }
            else
            {
                $filter->setFieldValue('specFieldValueID', SpecFieldValue::getInstanceByID((int)$value['specFieldValueID']));
                $filter->rangeDateStart->setNull();
                $filter->rangeDateEnd->setNull();
                $filter->rangeStart->setNull();
                $filter->rangeEnd->setNull();
            }
            
            
            $filter->setFieldValue('filterGroupID', $this);
            $filter->setFieldValue('position', $position);

            $filter->save();

            $position++;
        }
    }
}

?>
