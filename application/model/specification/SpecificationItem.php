<?php

ClassLoader::import("application.model.ActiveRecordModel");
ClassLoader::import("application.model.product.*");

include_once dirname(__file__) . '/Specification.php';

/**
 * Links a pre-defined attribute value to a product
 *
 * @package application.model.specification
 * @author Integry Systems <http://integry.com>   
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
	
	public static function restoreInstance(Product $product, SpecField $field, SpecFieldValue $value)
	{
		$inst = parent::getInstanceByID(__CLASS__, array('productID' => $product->getID(), 'specFieldID' => $field->getID(), 'specFieldValueID' => $value->getID()));
		$inst->specFieldValue->set($value);
		$inst->resetModifiedStatus();
	
		return $inst;
	}

	public static function getRecordCount(ARSelectFilter $filter)
	{
	    return parent::getRecordCount(__CLASS__, $filter);
	}

	/**
	 * @return ARSet
	 */
	public static function getRecordSet(ARSelectFilter $filter, $loadReferencedData = false)
	{
	    return parent::getRecordSet(__CLASS__, $filter, $loadReferencedData);
	}
	
	public function set(SpecFieldValue $value)
	{
	  	// test whether the value belongs to the same field
		if ($value->specField->get()->getID() != $this->specField->get()->getID())
	  	{
		    throw new Exception('Cannot assign SpecField:' . $value->specField->get()->getID() . ' value to SpecField:' . $this->specField->get()->getID());
		}
		
		if($value !== $this->specFieldValue->get()) $this->specFieldValue->set($value);
	}
	
	public function save()
	{
		parent::save();
	}

	public function toArray()
	{
		return $this->specFieldValue->get()->toArray();
	}
}

?>