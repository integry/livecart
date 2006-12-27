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
 *
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

	public static function getNewInstance(Product $product, SpecField $specField, SpecFieldValue $value)
	{
		$this->product = $product;
		$this->specField = $specField;
		$this->specFieldValue = $value;
	}
}

?>