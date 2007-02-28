<?php

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

	public static function restoreInstance(Product $product, SpecField $field, $value)
	{
		return parent::restoreInstance(__CLASS__, $product, $field, $value);
	}
}

?>