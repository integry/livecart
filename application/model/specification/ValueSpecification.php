<?php

ClassLoader::import('application.model.eavcommon.EavValueSpecificationCommon');

/**
 * An attribute value that is assigned to a particular product.
 * Concrete attribute value types (string, number, date, etc.) are defined by subclasses.
 *
 * @package application.model.specification
 * @author Integry Systems <http://integry.com>
 */
abstract class ValueSpecification extends EavValueSpecificationCommon
{
	public static function getFieldClass()
	{
		return 'SpecField';
	}

	public static function getOwnerClass()
	{
		return 'Product';
	}

	public static function getFieldIDColumnName()
	{
		return 'specFieldID';
	}

	public static function getOwnerIDColumnName()
	{
		return 'productID';
	}

	public static function getNewInstance($class, Product $product, SpecField $field, $value)
	{
		return parent::getNewInstance($class, $product, $field, $value);
	}

	public static function restoreInstance($class, Product $product, SpecField $field, $value)
	{
		return parent::restoreInstance($class, $product, $field, $value);
	}
}

?>