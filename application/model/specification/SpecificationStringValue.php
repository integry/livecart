<?php

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
		$schema = self::getSchemaInstance($className);
		$schema->setName("SpecificationStringValue");

		parent::defineSchema($className);		  	
		$schema->registerField(new ARField("value", ARArray::instance()));
	}

	public static function getNewInstance(Product $product, SpecField $field, $value)
	{
	  	return parent::getNewInstance(__CLASS__, $product, $field, $value);
	}
	
	public static function restoreInstance(Product $product, SpecField $field, $value)
	{
		$specItem = parent::restoreInstance(__CLASS__, $product, $field, $value);
		$specItem->value->set(unserialize($value));
		
		$specItem->resetModifiedStatus();

		return $specItem;
	}	
	
	public function getValueByLang($fieldName, $langCode, $returnDefaultIfEmpty = true)
	{
        return MultiLingualObject::getValueByLang($fieldName, $langCode, $returnDefaultIfEmpty);
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