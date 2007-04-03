<?php
ClassLoader::import("application.model.product.ProductParametersGroup");

class ProductFileGroup extends ProductParametersGroup
{
	private static $nextPosition = false;
	
	public static function defineSchema($className = __CLASS__)
	{
		$schema = parent::defineSchema($className);
		$schema->setName("ProductFileGroup");

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
	 * @return ProductFileGroup
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
	 * @return ProductFileGroup
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

		$filter->setOrder(new ARFieldHandle(__CLASS__, "position"), 'ASC');
		$filter->setCondition(new EqualsCond(new ARFieldHandle(__CLASS__, "productID"), $product->getID()));
		
		return $filter;
	}
	
	public static function mergeGroupsWithFields($groups, $fields)
	{
	    return parent::mergeGroupsWithFields(__CLASS__, $groups, $fields);
	}
}

?>