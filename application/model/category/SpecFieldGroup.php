<?php

ClassLoader::import("application.model.system.MultilingualObject");
ClassLoader::import("application.model.category.Category");
ClassLoader::import("application.model.system.ActiveRecordGroup");

/**
 * SpecFieldGroups allow to group related attributes (SpecFields) together.
 *
 * @package application.model.category
 * @author Integry Systems <http://integry.com>
 */
class SpecFieldGroup extends MultilingualObject 
{
	/**
	 * Define SpecFieldGroup database schema
	 */
	public static function defineSchema()
	{
		$schema = self::getSchemaInstance(__CLASS__);
		$schema->setName(__CLASS__);
		
		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("categoryID", "Category", "ID", "Category", ARInteger::instance()));
		$schema->registerField(new ARField("name", ARArray::instance()));
		$schema->registerField(new ARField("position", ARInteger::instance(2)));
	}
	
	/*####################  Static method implementations ####################*/

	/**
	 * Get new SpecFieldGroup active record instance
	 *
	 * @return SpecFieldGroup
	 */
	public static function getNewInstance(Category $category)
	{
		$inst = parent::getNewInstance(__CLASS__);
		$inst->category->set($category);

		return $inst;
	}
			
	/**
	 * Loads a set of active record instances of SpecFieldGroup by using a filter
	 *
	 * @param ARSelectFilter $filter
	 * @param bool $loadReferencedRecords
	 * @return ARSet
	 */
	public static function getRecordSet(ARSelectFilter $filter, $loadReferencedRecords = false)
	{
		return parent::getRecordSet(__CLASS__, $filter, $loadReferencedRecords);
	}

	/**
	 * Get specification group item instance
	 *
	 * @param int|array $recordID Record id
	 * @param bool $loadRecordData If true loads record's structure and data
	 * @param bool $loadReferencedRecords If true loads all referenced records
	 * @return SpecFieldGroup
	 */
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false)
	{
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords);
	}

	/**
	 * Get a set of SpecField records
	 *
	 * @param ARSelectFilter $filter
	 * @param bool $loadReferencedRecords Load referenced tables data
	 * @return array
	 */
	public static function getRecordSetArray(ARSelectFilter $filter, $loadReferencedRecords = false)
	{
		return parent::getRecordSetArray(__CLASS__, $filter, $loadReferencedRecords);
	}
	
	/*####################  Value retrieval and manipulation ####################*/		
	
	public static function mergeGroupsWithFields($groups, $fields)
	{
		return ActiveRecordGroup::mergeGroupsWithFields(__CLASS__, $groups, $fields);
	}	
	
	/*####################  Saving ####################*/	

	/**
	 * Delete spec field group from database
	 * 
	 * @param integer $id Spec field id
	 * @return boolean status
	 */
	public static function deleteById($id)
	{
		return parent::deleteByID(__CLASS__, (int)$id);
	}
	
	protected function insert()
	{
		// get max position
	  	$f = new ARSelectFilter();
	  	$f->setCondition(new EqualsCond(new ARFieldHandle(__CLASS__, 'categoryID'), $this->category->get()->getID()));
	  	$f->setOrder(new ARFieldHandle(__CLASS__, 'position'), 'DESC');
	  	$f->setLimit(1);
	  	$rec = ActiveRecord::getRecordSetArray(__CLASS__, $f);
		$position = (is_array($rec) && count($rec) > 0) ? $rec[0]['position'] + 1 : 1;	  
		
		$this->position->set($position);
		
		return parent::insert();
	}		
	
	/*####################  Get related objects ####################*/ 	
	
	/**
	 * Loads a set of spec field records for a group.
	 *
	 * @param boolean $includeParentFields 
	 * @param boolean $loadReferencedRecords 
	 * @return ARSet
	 */
	public function getSpecificationFieldSet($includeParentFields = false, $loadReferencedRecords = false)
	{
		ClassLoader::import("application.model.category.SpecField");
		return SpecField::getRecordSet($this->getSpecificationFilter($includeParentFields), $loadReferencedRecords);
	}

	/**
	 * Loads a set of spec field records for a group as array.
	 *
	 * @param boolean $includeParentFields 
	 * @param boolean $$loadReferencedRecords 
	 * @return array
	 */
	public function getSpecificationFieldArray($includeParentFields = false, $loadReferencedRecords = false)
	{
		ClassLoader::import("application.model.category.SpecField");
		return SpecField::getRecordSetArray($this->getSpecificationFilter($includeParentFields), $loadReferencedRecords);
	}
	
	/**
	 * Crates a select filter for specification fields related to group
	 *
	 * @param bool $includeParentFields
	 * @return ARSelectFilter
	 */
	private function getSpecificationFilter($includeParentFields)
	{
		$filter = new ARSelectFilter();
		$filter->setOrder(new ARFieldHandle("SpecField", "position"));
		$filter->setCondition(new EqualsCond(new ARFieldHandle("SpecField", "specFieldGroupID"), $this->getID()));

		return $filter;
	}
}
?>