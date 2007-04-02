<?php
ClassLoader::import("application.model.system.ActiveRecordModel");
ClassLoader::import("application.model.system.MultilingualObject");

class ProductRelationshipGroup extends MultilingualObject 
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("ProductRelationshipGroup");

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("productID", "Product", "ID", null, ARInteger::instance()));
		$schema->registerField(new ARField("position", ARInteger::instance()));
		$schema->registerField(new ARField("name", ARArray::instance()));
	}
	
	/**
	 * Load related products group record set
	 *
	 * @param ARSelectFilter $filter
	 * @param bool $loadReferencedRecords
	 *
	 * @return ARSet
	 */
	public static function getRecordSet(ARSelectFilter $filter, $loadReferencedRecords = false)
	{
		return parent::getRecordSet(__CLASS__, $filter, $loadReferencedRecords);
	}
	
	/**
	 * Get related products group active record by ID
	 *
	 * @param mixed $recordID
	 * @param bool $loadRecordData
	 * @param bool $loadReferencedRecords
	 *
	 * @return ProductRelationshipGroup
	 */
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false)
	{
	    return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords);
	}
	
	/**
	 * Creates a new related products group
	 *
	 * @param Product $product
	 * 
	 * @return ProductRelationshipGroup
	 */
	public static function getNewInstance(Product $product)
	{
		$group = parent::getNewInstance(__CLASS__);
		$group->product->set($product);

		return $group;
	}
	
	/**
	 * @return ARSet
	 */
	public static function getProductGroups(Product $product)
	{
	    return self::getRecordSet(self::getProductGroupsFilter($product), !ActiveRecord::LOAD_REFERENCES);
	}
	
	private static function getProductGroupsFilter(Product $product)
	{
	    $filter = new ARSelectFilter();
		$filter->setOrder(new ARFieldHandle("ProductRelationshipGroup", "position"), 'ASC');
		$filter->setCondition(new EqualsCond(new ARFieldHandle("ProductRelationshipGroup", "productID"), $product->getID()));
		
		return $filter;
	}    

	public static function mergeGroupsWithFields($groups, $fields)
	{
	    return ActiveRecordGroup::mergeGroupsWithFields(__CLASS__, $groups, $fields);
	}
}

?>