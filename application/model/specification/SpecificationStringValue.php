<?php

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
	
	public function setValueByLang($langCode, $value)
	{
		$currentValue = $this->value->get();
		if (!is_array($currentValue))
		{
		  	$currentValue = array();
		}
		
		$currentValue[$langCode] = $value;
		$this->value->set($currentValue);
	}	
}

?>