<?php

/**
 * An attribute value that is assigned to a particular product.
 * Concrete attribute value types (string, number, date, etc.) are defined by subclasses.
 *
 * @package application.model.specification
 * @author Integry Systems <http://integry.com>   
 */
abstract class ValueSpecification extends Specification
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		$schema->registerField(new ARPrimaryForeignKeyField("productID", "Product", "ID", null, ARInteger::instance()));
		$schema->registerField(new ARPrimaryForeignKeyField("specFieldID", "SpecField", "ID", null, ARInteger::instance()));
	}

	public static function getNewInstance($class, Product $product, SpecField $field, $value)
	{
		$specItem = parent::getNewInstance($class);
		$specItem->product->set($product);
		$specItem->specField->set($field);
		$specItem->value->set($value);

		return $specItem;
	}
	
	public static function restoreInstance($class, Product $product, SpecField $field, $value)
	{
		$specItem = parent::getInstanceByID($class, array('productID' => $product->getID(), 'specFieldID' => $field->getID()));
		$specItem->value->set($value);
		$specItem->resetModifiedStatus();

		return $specItem;
	}

	public static function transformArray($array, $class = __CLASS__)
	{
		unset($array['Product']);
		unset($array['SpecField']);
		return MultiLingualObject::transformArray($array, $class);
	}

	public function toArray()
	{	
		$arr  = parent::toFlatArray();
		$arr['SpecField'] = $this->specField->get()->toArray();
		
		return $arr;
	}	


}

?>