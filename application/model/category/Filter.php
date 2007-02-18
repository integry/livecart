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
	 * Create an ActiveRecord Condition object to use for product selection
	 *
	 * @return Condition
	 */
	public function getCondition()
	{
		$specField = $this->filterGroup->get()->specField->get();
		
		echo $specField->getValueByLang('name', 'en') . ' - ' . $specField->type->get().'<br>';
		
		// selector values
		if ($specField->isSelector())
		{
			$valueID = $this->specFieldValue->get()->getID();
			if ($specField->isMultiValue->get())
			{
				$cond = new EqualsCond(new ARExpressionHandle('specItemTable_' . $specField->getID() . '_' . $valueID . '.specFieldValueID'), $valueID);							
			}
			else
			{
				$cond = new EqualsCond(new ARExpressionHandle('specItemTable_' . $specField->getID() . '.specFieldValueID'), $valueID);			
			}
		}
		
		// number range
		elseif ($specField->isSimpleNumbers())
		{
			$field = new ARExpressionHandle('specTable_' . $specField->getID() . '.value');
			
			$conditions = array();
			
			if ($this->rangeStart->get())
			{
				$conditions[] = new EqualsOrMoreCond($field, $this->rangeStart->get());
			}
			
			if ($this->rangeEnd->get())
			{
				$conditions[] = new EqualsOrLessCond($field, $this->rangeEnd->get());
			}
		
			$cond = Condition::mergeFromArray($conditions);			
		}
		
		// date range
		elseif ($specField->isDate())
		{
			$field = new ARExpressionHandle('specTable_' . $specField->getID() . '.value');
			
			$conditions = array();
			
			if ($this->rangeDateStart->get())
			{
				$conditions[] = new EqualsOrMoreCond($field, $this->rangeDateStart->get());
			}
			
			if ($this->rangeDateEnd->get())
			{
				$conditions[] = new EqualsOrLessCond($field, $this->rangeDateEnd->get());
			}
		
			$cond = Condition::mergeFromArray($conditions);			
		}
		
		else
		{
			throw new ApplicationException('Filter type not supported');
		}	
		
		return $cond;
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