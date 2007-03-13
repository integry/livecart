<?php
class RelatedProductGroup extends MultilingualObject
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("RelatedProductGroup");

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("ProductID", "Product", "ID", null, ARInteger::instance()));
		$schema->registerField(new ARField("position", ARInteger::instance()));
		$schema->registerField(new ARField("name", ARArray::instance()));
	}
	
	/**
	 * Get related products group active record by ID
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
	 * Creates a new related products group
	 *
	 * @param Product $product
	 * 
	 * @return RelatedProductGroup
	 */
	public static function getNewInstance(Product $product)
	{
		$group = parent::getNewInstance(__CLASS__);
		$group->product->set($product);

		return $group;
	}
}

?>