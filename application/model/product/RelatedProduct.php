<?php
class RelatedProduct extends ActiveRecord 
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("RelatedProduct");

		$schema->registerField(new ARPrimaryForeignKeyField("ProductID", 		"Product",   	 	   "ID", "Product", ARInteger::instance()));
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
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false)
	{
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
		$relationship = parent::getNewInstance(__CLASS__);
		
		
		$relationship->product->set($product);
		$relationship->relatedProduct->set($related);
		
		return $relationship;
	}
}

?>