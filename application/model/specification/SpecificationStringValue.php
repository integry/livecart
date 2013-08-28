<?php

ClassLoader::import('application.model.specification.ValueSpecification');

/**
 * String attribute value assigned to a particular product.
 *
 * @package application.model.specification
 * @author Integry Systems <http://integry.com>
 */
class SpecificationStringValue extends ValueSpecification
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = parent::defineSchema($className);
		public $value;
	}

	public static function getNewInstance(Product $product, SpecField $field, $value)
	{
	  	return parent::getNewInstance(__CLASS__, $product, $field, $value);
	}

	public static function restoreInstance(Product $product, SpecField $field, $value)
	{
		$specItem = parent::restoreInstance(__CLASS__, $product, $field, $value);
		$specItem->value = unserialize($value));

		$specItem->resetModifiedStatus();

		return $specItem;
	}
}

?>