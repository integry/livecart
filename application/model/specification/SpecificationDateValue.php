<?php

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
	
	public static function restoreInstance(Product $product, SpecField $field, $value)
	{
		return parent::restoreInstance(__CLASS__, $product, $field, $value);
	}
}

?>