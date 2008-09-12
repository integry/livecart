<?php

ClassLoader::import('application.model.eavcommon.EavValueSpecificationCommon');

/**
 * An attribute value that is assigned to a particular product.
 * Concrete attribute value types (string, number, date, etc.) are defined by subclasses.
 *
 * @package application.model.specification
 * @author Integry Systems <http://integry.com>
 */
abstract class EavValueSpecification extends EavValueSpecificationCommon
{
	public static function getFieldClass()
	{
		return 'EavField';
	}

	public static function getOwnerClass()
	{
		return 'EavObject';
	}

	public static function getFieldIDColumnName()
	{
		return 'fieldID';
	}

	public static function getOwnerIDColumnName()
	{
		return 'objectID';
	}

	public function getOwnerVarName()
	{
		return 'object';
	}

	public function getField()
	{
		return $this->field;
	}

	public static function getNewInstance($class, EavObject $product, EavField $field, $value)
	{
		return parent::getNewInstance($class, $product, $field, $value);
	}

	public static function restoreInstance($class, EavObject $product, EavField $field, $value)
	{
		return parent::restoreInstance($class, $product, $field, $value);
	}
}

?>