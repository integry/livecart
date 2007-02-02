<?php

ClassLoader::import("application.model.ActiveRecordModel");
ClassLoader::import("application.model.product.*");

/**
 * Product specification data container
 * Contains a relation between specification fields (attributes), assigned values and products
 * (kind of "feature table")
 *
 * @author Saulius Rupainis <saulius@integry.net>
 * @package application.model.product
 */
class SpecificationItem extends Specification
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("SpecificationItem");

		$schema->registerField(new ARPrimaryForeignKeyField("specFieldID", "SpecField", "ID", null, ARInteger::instance()));
		$schema->registerField(new ARPrimaryForeignKeyField("specFieldValueID", "SpecFieldValue", "ID", "SpecFieldValue", ARInteger::instance()));
		$schema->registerField(new ARPrimaryForeignKeyField("productID", "Product", "ID", null, ARInteger::instance()));
	}

	public static function getNewInstance(Product $product, SpecField $field, SpecFieldValue $value)
	{
		$specItem = parent::getNewInstance(__CLASS__);
		$specItem->product->set($product);
		$specItem->specField->set($field);
		$specItem->specFieldValue->set($value);

		return $specItem;
	}
	
	public function setValue(SpecFieldValue $value)
	{
	  	// test whether the value belongs to the same field
		if ($value->specField->get()->getID() != $this->specField->get()->getID())
	  	{
		    throw new Exception('Cannot assign SpecField:' . $value->specField->get()->getID() . ' value to SpecField:' . $this->specField->get()->getID());
		}

		$this->specFieldValue->set($value);
	}
	
	public function toArray()
	{
		$ret = $this->specFieldValue->get()->toArray();

		return $ret;
	}	
	
}

?>