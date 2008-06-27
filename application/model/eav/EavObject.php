<?php

ClassLoader::import('application.model.ActiveRecordModel');

class EavObject extends ActiveRecordModel
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("categoryID", "Category", "ID", null, ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("customerOrderID", "CustomerOrder", "ID", null, ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("manufacturerID", "Manufacturer", "ID", null, ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("userID", "User", "ID", null, ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("userGroupID", "UserGroup", "ID", null, ARInteger::instance()));
	}

	public static function getInstance(EavAble $parent)
	{
		if (!$classId = EavField::getClassID(get_class($parent)))
		{
			throw new ApplicationException(get_class($parent) . ' is not supported as a valid EAV object');
		}

		$s = self::getRecordSet(__CLASS__, new ARSelectFilter(new EqualsCond(new ARFieldHandle(__CLASS__, self::getInstanceField($parent)), $parent->getID())));
		if ($s->size())
		{
			return $s->get(0);
		}
		else
		{
			return self::getNewInstance($parent);
		}
	}

	public static function getNewInstance(EavAble $parent)
	{
		$field = self::getInstanceField($parent);
		$instance = parent::getNewInstance(__CLASS__);
		$instance->$field->set($parent);

		return $instance;
	}

	private function getInstanceField(ActiveRecordModel $instance)
	{
		$class = get_class($instance);
		return strtolower(substr($class, 0, 1)) . substr($class, 1) . 'ID';
	}
}

?>