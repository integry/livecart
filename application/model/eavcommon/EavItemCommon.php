<?php

ClassLoader::import('application.model.eavcommon.EavSpecificationCommon');

/**
 * Links a pre-defined attribute value to a product
 *
 * @package application.model.specification
 * @author Integry Systems <http://integry.com>
 */
abstract class EavItemCommon extends EavSpecificationCommon
{
	public abstract static function getFieldClass();

	public abstract static function getOwnerClass();

	public abstract static function getValueClass();

	public abstract static function getFieldIDColumnName();

	public abstract static function getOwnerIDColumnName();

	public abstract static function getValueIDColumnName();

	public abstract function getValue();

	public static function defineSchema($className)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		$schema->registerField(new ARPrimaryForeignKeyField(call_user_func(array($className, 'getFieldIDColumnName')), call_user_func(array($className, 'getFieldClass')), "ID", null, ARInteger::instance()));
		$schema->registerField(new ARPrimaryForeignKeyField(call_user_func(array($className, 'getValueIDColumnName')), call_user_func(array($className, 'getValueClass')), "ID", null, ARInteger::instance()));
		$schema->registerField(new ARPrimaryForeignKeyField(call_user_func(array($className, 'getOwnerIDColumnName')), call_user_func(array($className, 'getOwnerClass')), "ID", null, ARInteger::instance()));
	}

	public static function getNewInstance($className, ActiveRecordModel $owner, EavFieldCommon $field, EavValueCommon $value)
	{
		$specItem = parent::getNewInstance($className);
		$specItem->getOwner()->set($owner);
		$specItem->getField()->set($field);
		$specItem->getValue()->set($value);

		return $specItem;
	}

	public static function restoreInstance($className, ActiveRecordModel $owner, EavFieldCommon $field, EavValueCommon $value)
	{
		$inst = parent::getInstanceByID($className, array(call_user_func(array($className, 'getOwnerIDColumnName')) => $owner->getID(), call_user_func(array($className, 'getFieldIDColumnName')) => $field->getID(), call_user_func(array($className, 'getValueIDColumnName')) => $value->getID()));
		$inst->getValue()->set($value);
		$inst->resetModifiedStatus();

		return $inst;
	}

	public function set(EavValueCommon $value)
	{
	  	// test whether the value belongs to the same field
		if ($value->getField()->get()->getID() != $this->getField()->get()->getID())
	  	{
			$class = get_class($value->getField()->get());
			throw new Exception('Cannot assign ' . $class . ':' . $value->getField()->get()->getID() . ' value to ' . $class . ':' . $this->getField()->get()->getID());
		}

		if($value !== $this->getValue()->get()) $this->getValue()->set($value);
	}

	public function getField()
	{
		return $this->getValue($this->getFieldIDColumnName);
	}

	public function save($params = null)
	{
		if ($this->value && !$this->value->get())
		{
			return;
		}

		return parent::save($params);
	}

	public function toArray()
	{
		if ($value = $this->getValue()->get())
		{
			return $this->getValue()->get()->toArray();
		}
	}

	public function __destruct()
	{
		$this->getValue()->destructValue();
		parent::__destruct();
	}




}

?>