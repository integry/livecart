<?php
class RelatedProduct extends ActiveRecord 
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("RelatedProduct");

		$schema->registerField(new ARPrimaryForeignKeyField("productID", 		"Product",   	 	   "ID", "Product", ARInteger::instance()));
		$schema->registerField(new ARPrimaryForeignKeyField("relatedProductID", "Product", 			   "ID", "Product", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("relatedProductGroupID", 	"RelatedProductGroup", "ID", "RelatedProductGroup", ARInteger::instance()));
		$schema->registerField(new ARField("position",                                                             ARInteger::instance()));
	}
	
	/**
	 * Get related product active record by ID
	 *
	 * @param mixed $recordID
	 * @param bool $loadRecordData
	 * @param bool $loadReferencedRecords
	 *
	 * @return RelatedProductGroup
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
	 * @return RelatedProduct
	 */
	public static function getNewInstance(Product $product, Product $related)
	{
		if(null == $product || null == $related || $product === $related || $product->getID() == $related->getID())
		{
		    throw new Exception('Expected two different products when creating a relationship');
		}
		
	    $relationship = parent::getNewInstance(__CLASS__);
		
		$relationship->product->set($product);
		$relationship->relatedProduct->set($related);
		
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
	
	public static function getRelationships(Product $product)
	{
	    return RelatedProduct::getRecordSet(self::getRelatedProductsSetFilter($product), ActiveRecord::LOAD_REFERENCES);
	}
	
	private static function getRelatedProductsSetFilter(Product $product)
	{
	    $filter = new ARSelectFilter();

		$filter->setOrder(new ARFieldHandle("RelatedProduct", "position"), 'ASC');
		$filter->setCondition(new EqualsCond(new ARFieldHandle("RelatedProduct", "productID"), $product->getID()));
		
		return $filter;
	}
}

?>