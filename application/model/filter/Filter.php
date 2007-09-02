<?php

ClassLoader::import("application.model.system.MultilingualObject");
ClassLoader::import('application.model.filter.SpecificationFilterInterface');
ClassLoader::import('application.model.filter.FilterGroup');
ClassLoader::import('application.model.category.SpecField');
ClassLoader::import('application.model.category.SpecFieldValue');
ClassLoader::import('application.helper.CreateHandleString');

/**
 * Filters allow to filter the product list by specific product attribute values.
 * Common Filters (that are based on the same attribute) are grouped into FilterGroups.
 * 
 * There are several other types of filters, but Filter class defines filters for attributes
 * that can be filtered by a value range (weight, size, date, etc.)
 * 
 * @package application.model.filter
 * @author Integry Systems <http://integry.com>
 */
class Filter extends MultilingualObject implements SpecificationFilterInterface
{
    /**
     * Define Filter schema
     */
	public static function defineSchema()
	{
		$schema = self::getSchemaInstance(__CLASS__);
		$schema->setName(__CLASS__);

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("filterGroupID", "FilterGroup", "ID", "FilterGroup", ARInteger::instance()));
		$schema->registerField(new ARField("name", ARArray::instance()));
		$schema->registerField(new ARField("position", ARInteger::instance(2)));
		$schema->registerField(new ARField("rangeStart", ARFloat::instance(40)));
		$schema->registerField(new ARField("rangeEnd", ARFloat::instance(40)));
		$schema->registerField(new ARField("rangeDateStart", ARDate::instance()));
		$schema->registerField(new ARField("rangeDateEnd", ARDate::instance()));
	}

	/*####################  Static method implementations ####################*/	

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
	public static function getNewInstance(FilterGroup $filterGroup)
	{
		$inst = parent::getNewInstance(__CLASS__);
		$inst->filterGroup->set($filterGroup);
		return $inst;
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

	/*####################  FilterInterface method implementations ####################*/	

	/**
	 * Create an ActiveRecord Condition object to use for product selection
	 *
	 * @return Condition
	 */
	public function getCondition()
	{
		$specField = $this->filterGroup->get()->specField->get();

		// number range
		if ($specField->isSimpleNumbers())
		{
			$field = new ARExpressionHandle($this->getJoinAlias() . '.value');
			
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
			$field = new ARExpressionHandle($this->getJoinAlias() . '.value');
			
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
     *	Adds JOIN definition to ARSelectFilter to retrieve product attribute value for the particular SpecField
     *	
     *	@param	ARSelectFilter	$filter	Filter instance
     */
	public function defineJoin(ARSelectFilter $filter)
    {
	  	$field = $this->filterGroup->get()->specField->get();
		$table = $this->getJoinAlias();
		$filter->joinTable($field->getValueTableName(), 'Product', 'productID AND ' . $table . '.SpecFieldID = ' . $field->getID(), 'ID', $table);				  	  	
	}

	protected function getJoinAlias()
	{
		return 'specField_' . $this->getSpecField()->getID(); 			 	  	
	}

	public function getSpecField()
	{
		return $this->filterGroup->get()->specField->get();
	}

	/*####################  Saving ####################*/	

	protected function insert()
	{
		$this->position->set(100000);  	
		return parent::insert();
	}

	/**
	 * Delete Filter from database
	 */
	public static function deleteByID($id)
	{
	    parent::deleteByID(__CLASS__, (int)$id);
	}
	
	/*####################  Data array transformation ####################*/	

	public static function transformArray($array, ARSchema $schema)
	{		
		$array = parent::transformArray($array, $schema);
		$array['handle'] = createHandleString($array['name_lang']);
		return $array;
	}
}

?>