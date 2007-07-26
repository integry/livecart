<?php

/**
 * Assigns a related (recommended) product to a particular product
 * 
 * @package application.model.product
 * @author Integry Systems <http://integry.com>   
 */
class ProductRelationship extends ActiveRecord 
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("ProductRelationship");

		$schema->registerField(new ARPrimaryForeignKeyField("productID", "Product", "ID", "Product", ARInteger::instance()));
		$schema->registerField(new ARPrimaryForeignKeyField("relatedProductID", "Product", "ID", "Product", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("productRelationshipGroupID", "ProductRelationshipGroup", "ID", "ProductRelationshipGroup", ARInteger::instance()));
		$schema->registerField(new ARField("position",  ARInteger::instance()));
	}
	
	/**
	 * Get related product active record by ID
	 *
	 * @param mixed $recordID
	 * @param bool $loadRecordData
	 * @param bool $loadReferencedRecords
	 *
	 * @return ProductRelationshipGroup
	 */
	public static function getInstance(Product $product, Product $relatedProduct, $loadRecordData = false, $loadReferencedRecords = false)
	{
	    $recordID = array(
			'productID' => $product->getID(),
	    	'relatedProductID' => $relatedProduct->getID()
	    );
	    
	    return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords);
	}
	
	/**
	 * Creates a new related product
	 *
	 * @param Product $product
	 * @param Product $relatedProduct
	 * 
	 * @return ProductRelationship
	 */
	public static function getNewInstance(Product $product, Product $related, ProductRelationshipGroup $group = null)
	{
		if(null == $product || null == $related || $product === $related || $product->getID() == $related->getID())
		{
		    require_once('ProductRelationshipException.php');
			throw new ProductRelationshipException('Expected two different products when creating a relationship');
		}
		
	    $relationship = parent::getNewInstance(__CLASS__);
		
		$relationship->product->set($product);
		$relationship->relatedProduct->set($related);
		if(!is_null($group))
		{
		    $relationship->productRelationshipGroup->set($group);
		}
		
		return $relationship;
	}
	
	/**
	 * Get relationships set
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
	 * Gets an existing relationship instance
	 *
	 * @param mixed $recordID
	 * @param bool $loadRecordData
	 * @param bool $loadReferencedRecords
	 * @param array $data	Record data array (may include referenced record data)
	 *
	 * @return ProductRelationship
	 */
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false, $data = array())
	{
	    return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords, $data);
	}
	
	/**
	 * Get product relationships
	 *
	 * @param Product $product
	 * @return ARSet
	 */
	public static function getRelationships(Product $product, $loadReferencedRecords = array('RelatedProduct' => 'Product'))
	{
	    return self::getRecordSet(self::getRelatedProductsSetFilter($product), $loadReferencedRecords);
	}
	
	/**
	 * Get product relationships
	 *
	 * @param Product $product
	 * @return array
	 */
	public static function getRelationshipsArray(Product $product, $loadReferencedRecords = array('RelatedProduct' => 'Product'))
	{
	    return parent::getRecordSetArray(__CLASS__, self::getRelatedProductsSetFilter($product), $loadReferencedRecords);
	}
	
	public static function hasRelationship(Product $product, Product $relatedToProduct)
	{
	    $recordID = array(
			'productID' => $product->getID(), 
			'relatedProductID' => $relatedToProduct->getID()
	    );
	    	    
	    if(self::retrieveFromPool(__CLASS__, $recordID)) return true;
	    if(self::objectExists(__CLASS__, $recordID)) return true;
	    
	    return false;
	}
	
	private static function getRelatedProductsSetFilter(Product $product)
	{
	    $filter = new ARSelectFilter();

		$filter->joinTable('ProductRelationshipGroup', 'ProductRelationship', 'ID', 'productRelationshipGroupID');		
		$filter->setOrder(new ARFieldHandle("ProductRelationshipGroup", "position"), 'ASC');			
		$filter->setOrder(new ARFieldHandle("ProductRelationship", "position"), 'ASC');	
		$filter->setCondition(new EqualsCond(new ARFieldHandle("ProductRelationship", "productID"), $product->getID()));
		
		return $filter;
	}
	
	protected function insert()
	{
	  	// get max position
	  	$f = self::getRelatedProductsSetFilter($this->product->get());
	  	$f->setLimit(1);
	  	$rec = ActiveRecord::getRecordSetArray('ProductRelationship', $f);
		$position = (is_array($rec) && count($rec) > 0) ? $rec[0]['position'] + 1 : 0;
		$this->position->set($position);	
		
		return parent::insert();
	}	
}

?>