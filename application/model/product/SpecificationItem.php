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
class SpecificationItem extends ActiveRecordModel
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
		$specItem->product = $product;
		$specItem->specField = $field;
		$specItem->specFieldValue = $value;

		return $specItem;
	}
}

/**
 * Product specification data container
 * Contains a relation between specification fields (attributes), assigned values and products
 * (kind of "feature table")
 *
 * @author Saulius Rupainis <saulius@integry.net>
 * @package application.model.product
 */
abstract class ValueSpecification extends ActiveRecordModel
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		$schema->registerField(new ARPrimaryForeignKeyField("productID", "Product", "ID", null, ARInteger::instance()));
		$schema->registerField(new ARPrimaryForeignKeyField("specFieldID", "SpecField", "ID", null, ARInteger::instance()));
	}

	public static function getNewInstance($class, Product $product, SpecField $field, $value)
	{
		$specItem = parent::getNewInstance($class);
		$specItem->product->set($product);
		$specItem->specField->set($field);
		$specItem->value->set($value);

		return $specItem;
	}
}

class SpecificationNumericValue extends ValueSpecification
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);

		parent::defineSchema($className);		  	
		$schema->registerField(new ARField("value", ARInteger::instance()));
	}

	public static function getNewInstance(Product $product, SpecField $field, $value)
	{
	  	return parent::getNewInstance(__CLASS__, $product, $field, $value);
	}
}

class SpecificationStringValue extends ValueSpecification
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("SpecificationStringValue");

		parent::defineSchema($className);		  	
		$schema->registerField(new ARField("value", ARArray::instance()));
	}

	public static function getNewInstance(Product $product, SpecField $field, $value)
	{
	  	return parent::getNewInstance(__CLASS__, $product, $field, $value);
	}
}

class SpecificationDateValue extends ValueSpecification
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("SpecificationDateValue");

		parent::defineSchema($className);		  	
		$schema->registerField(new ARField("value", ARDate::instance()));
	}

	public static function getNewInstance(Product $product, SpecField $field, $value)
	{
	  	return parent::getNewInstance(__CLASS__, $product, $field, $value);
	}
}

?>