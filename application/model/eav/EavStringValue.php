<?php


/**
 * String attribute value assigned to a particular product.
 *
 * @package application/model/specification
 * @author Integry Systems <http://integry.com>
 */
class EavStringValue extends EavValueSpecification
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = parent::defineSchema($className);
		$schema->registerField(new ARField("value", ARArray::instance()));
	}

	public static function getNewInstance(EavObject $object, EavField $field, $value)
	{
	  	return parent::getNewInstance(__CLASS__, $object, $field, $value);
	}

	public static function restoreInstance(EavObject $object, EavField $field, $value)
	{
		$specItem = parent::restoreInstance(__CLASS__, $object, $field, $value);
		$specItem->value->set(unserialize($value));

		$specItem->resetModifiedStatus();

		return $specItem;
	}
}

?>