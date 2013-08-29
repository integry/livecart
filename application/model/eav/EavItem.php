<?php


/**
 * Links a pre-defined attribute value to a product
 *
 * @package application/model/specification
 * @author Integry Systems <http://integry.com>
 */
class EavItem extends EavItemCommon
{
	public static function getFieldClass()
	{
		return 'EavField';
	}

	public static function getOwnerClass()
	{
		return 'EavObject';
	}

	public static function getValueClass()
	{
		return 'EavValue';
	}

	public static function getFieldIDColumnName()
	{
		return 'fieldID';
	}

	public static function getOwnerIDColumnName()
	{
		return 'objectID';
	}

	public static function getValueIDColumnName()
	{
		return 'valueID';
	}

	public function getOwnerVarName()
	{
		return 'object';
	}

	public function getField()
	{
		return $this->field;
	}

	public function getValue()
	{
		return $this->value;
	}

	public static function defineSchema($className = __CLASS__)
	{
		return parent::defineSchema($className);
	}

	public static function getNewInstance(EavObject $product, EavField $field, EavValue $value)
	{
		return parent::getNewInstance(__CLASS__, $product, $field, $value);
	}

	public static function restoreInstance(EavObject $product, EavField $field, EavValue $value)
	{
		return parent::restoreInstance(__CLASS__, $product, $field, $value);
	}

	public function set(EavValue $value)
	{
	  	return parent::set($value);
	}

	public function toArray()
	{
		$arr = parent::toArray();
		$arr['EavField'] = $arr['Field'];
		return $arr;
	}
}

?>