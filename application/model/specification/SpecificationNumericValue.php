<?php


/**
 * Numeric attribute value assigned to a particular product.
 *
 * @package application.model.specification
 * @author Integry Systems <http://integry.com>
 */
class SpecificationNumericValue extends ValueSpecification
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = parent::defineSchema($className);
		$schema->registerField(new ARField("value", ARInteger::instance()));
	}

	public static function getNewInstance(Product $product, SpecField $field, $value)
	{
	  	return parent::getNewInstance(__CLASS__, $product, $field, $value);
	}

	public static function restoreInstance(Product $product, SpecField $field, $value)
	{
		return parent::restoreInstance(__CLASS__, $product, $field, $value);
	}
}

?>