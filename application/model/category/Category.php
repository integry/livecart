<?php

ClassLoader::import("application.model.system.ActiveTreeNode");

/**
 * ...
 *
 * @package application.model.category
 */
class Category extends ActiveTreeNode 
{
	/**
	 * Define database schema used by this active record instance
	 *
	 * @param string $className Schema name
	 */
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("Category");
		parent::defineSchema($className);
		
		$schema->registerField(new ARField("name", ARArray::instance()));
		$schema->registerField(new ARField("description", ARArray::instance()));
		$schema->registerField(new ARField("keywords", ARArray::instance()));
		$schema->registerField(new ARField("isActive", ARBool::instance()));
		$schema->registerField(new ARField("handle", ARVarchar::instance(40)));
	}

	/**
	 * Get catalog item instance
	 *
	 * @param int|array $recordID Record id
	 * @param bool $loadRecordData If true loads record's structure and data
	 * @param bool $loadReferencedRecords If true loads all referenced records
	 * @return Catalog
	 */
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false)
	{
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords);
	}

	public function getSpecFieldList()
	{
		$filter = new ARSelectFilter();
		$filter->setOrder(new ARFieldHandle("SpecField", "position"));
		$filter->setCondition(new EqualsCond(new ARFieldHandle("SpecField", "categoryID"), $this->getID()));

		return SpecField::getRecordSetArray($filter);
	}

}

/*
class MultilingualCategory extends MultilingualDataObject
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance("Category");
		$schema->setName("Category");
	}
}
*/

/*
class Category extends ARTreeNode
{
	private $multilingualCategory = null;
	
	protected function __construct()
	{
		$this->multilingualCategory = MultilingualCategory::getInstanceByID();
	}
	
	public function lang($langCode)
	{
		return $this->multilingualCategory->lang($langCode);
	}
	
	public static function getRecordSet($filter)
	{
		$recordSet = parent::getRecordSet(__CLASS__);
	}
	
	public static function defineSchema()
	{
		
	}
}
*/

?>