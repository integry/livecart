<?php

ClassLoader::import('application.model.eavcommon.EavItemCommon');
ClassLoader::import('application.model.product.Product');
ClassLoader::import('application.model.category.SpecField');
ClassLoader::import('application.model.category.SpecFieldValue');

/**
 * Links a pre-defined attribute value to a product
 *
 * @package application.model.specification
 * @author Integry Systems <http://integry.com>
 */
class SpecificationItem extends EavItemCommon
{
	public static function getFieldClass()
	{
		return 'SpecField';
	}

	public static function getOwnerClass()
	{
		return 'Product';
	}

	public static function getValueClass()
	{
		return 'SpecFieldValue';
	}

	public static function getFieldIDColumnName()
	{
		return 'specFieldID';
	}

	public static function getOwnerIDColumnName()
	{
		return 'productID';
	}

	public static function getValueIDColumnName()
	{
		return 'specFieldValueID';
	}

	public function getOwnerVarName()
	{
		return 'product';
	}

	public function getField()
	{
		return $this->specField;
	}

	public function getValue()
	{
		return $this->specFieldValue;
	}

	public static function defineSchema($className = __CLASS__)
	{
		return parent::defineSchema($className);
	}

	public static function getNewInstance(Product $product, SpecField $field, SpecFieldValue $value)
	{
		return parent::getNewInstance(__CLASS__, $product, $field, $value);
	}

	public static function restoreInstance(Product $product, SpecField $field, SpecFieldValue $value)
	{
		return parent::restoreInstance(__CLASS__, $product, $field, $value);
	}

	public function set(SpecFieldValue $value)
	{
	  	return parent::set($value);
	}

}

?>