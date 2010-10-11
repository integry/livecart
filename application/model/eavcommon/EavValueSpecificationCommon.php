<?php

ClassLoader::import('application.model.eavcommon.EavSpecificationCommon');

/**
 * An attribute value that is assigned to a particular product.
 * Concrete attribute value types (string, number, date, etc.) are defined by subclasses.
 *
 * @package application.model.specification
 * @author Integry Systems <http://integry.com>
 */
abstract class EavValueSpecificationCommon extends EavSpecificationCommon
{
	public abstract static function getFieldClass();

	public abstract static function getOwnerClass();

	public abstract static function getFieldIDColumnName();

	public abstract static function getOwnerIDColumnName();

	public static function defineSchema($className)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		$schema->registerField(new ARPrimaryForeignKeyField(call_user_func(array($className, 'getOwnerIDColumnName')), call_user_func(array($className, 'getOwnerClass')), "ID", null, ARInteger::instance()));
		$schema->registerField(new ARPrimaryForeignKeyField(call_user_func(array($className, 'getFieldIDColumnName')), call_user_func(array($className, 'getFieldClass')), "ID", null, ARInteger::instance()));

		return $schema;
	}

	public static function getNewInstance($class, ActiveRecordModel $owner, EavFieldCommon $field, $value)
	{
		$specItem = parent::getNewInstance($class);
		$specItem->getOwner()->set($owner);
		$specItem->getField()->set($field);
		$specItem->value->set($value);

		return $specItem;
	}

	public static function restoreInstance($className, ActiveRecordModel $owner, EavFieldCommon $field, $value)
	{
		$specItem = parent::getInstanceByID($className, array(call_user_func(array($className, 'getOwnerIDColumnName')) => $owner->getID(),
														  call_user_func(array($className, 'getFieldIDColumnName')) => $field->getID()));
		$specItem->value->set($value);
		$specItem->resetModifiedStatus();

		return $specItem;
	}

	public function getValueByLang($fieldName, $langCode = null, $returnDefaultIfEmpty = true)
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

	public static function transformArray($array, ARSchema $schema)
	{
		unset($array[call_user_func(array($schema->getName(), 'getOwnerClass'))]);
		unset($array[call_user_func(array($schema->getName(), 'getFieldClass'))]);
		return MultiLingualObject::transformArray($array, $schema);
	}

	public function toArray()
	{
		$arr  = parent::toFlatArray();
		$arr[$this->getFieldClass()] = $this->getFieldInstance()->toArray();

		if ($arr['value'] && ($this->value->getField()->getDataType() instanceof ARDate))
		{
			$arr['formatted'] = $this->getApplication()->getLocale()->getFormattedTime(strtotime($arr['value']));
		}

		return $arr;
	}

	public function getValue()
	{
		return $this->value->get();
	}
}

?>