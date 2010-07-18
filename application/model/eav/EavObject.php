<?php

ClassLoader::import('application.model.ActiveRecordModel');
ClassLoader::import('application.model.category.Category');
ClassLoader::import('application.model.order.CustomerOrder');
ClassLoader::import('application.model.product.Manufacturer');
ClassLoader::import('application.model.user.User');
ClassLoader::import('application.model.user.UserGroup');
ClassLoader::import('application.model.eav.EavField');

class EavObject extends ActiveRecordModel
{
	private $stringIdentifier;
	private $parent;

	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("categoryID", "Category", "ID", null, ARInteger::instance()), false);
		$schema->registerField(new ARForeignKeyField("customerOrderID", "CustomerOrder", "ID", null, ARInteger::instance()), false);
		$schema->registerField(new ARForeignKeyField("manufacturerID", "Manufacturer", "ID", null, ARInteger::instance()), false);
		$schema->registerField(new ARForeignKeyField("userID", "User", "ID", null, ARInteger::instance()), false);
		$schema->registerField(new ARForeignKeyField("userAddressID", "UserAddress", "ID", null, ARInteger::instance()), false);
		$schema->registerField(new ARForeignKeyField("userGroupID", "UserGroup", "ID", null, ARInteger::instance()), false);
		$schema->registerField(new ARForeignKeyField("transactionID", "Transaction", "ID", null, ARInteger::instance()), false);
		$schema->registerField(new ARField("classID", ARInteger::instance(2)));
	}

	public static function getInstance(EavAble $parent)
	{
		if (!$classId = EavField::getClassID(get_class($parent)))
		{
			if (!EavField::getClassNameById($classId))
			{
				throw new ApplicationException(get_class($parent) . ' is not supported as a valid EAV object');
			}
		}

		if ($parent->eavObject->get())
		{
			$parent->eavObject->get()->classID->set(EavField::getClassID(get_class($parent)));
			return $parent->eavObject->get();
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
		$instance->classID->set(EavField::getClassID($parent));

		$instance->parent = $parent;
		$parent->eavObject->set($instance);

		return $instance;
	}

	public static function getInstanceByIdentifier($stringIdentifier)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->classID->set(0);
		$instance->setStringIdentifier($stringIdentifier);
		return $instance;
	}

	public function getClassField($className)
	{
		return strtolower(substr($className, 0, 1)) . substr($className, 1) . 'ID';
	}

	public function setStringIdentifier($stringIdentifier)
	{
		$this->stringIdentifier = $stringIdentifier;
	}

	public function getStringIdentifier()
	{
		return $this->stringIdentifier;
	}

	public function getOwner()
	{
		foreach ($this->getSchema()->getForeignKeyList() as $key => $field)
		{
			if ($this->data[$key]->get())
			{
				return $this->data[$key]->get();
			}
		}
	}

	public function serialize($skippedRelations = array(), $properties = array())
	{
		if ($this->getOwner())
		{
			$skippedRelations[] = $this->getInstanceField($this->getOwner());
		}

		return parent::serialize($skippedRelations, $properties);
	}

	protected function insert()
	{
		parent::insert();
		$this->parent->save();
	}

	private function getInstanceField(ActiveRecordModel $instance)
	{
		return self::getClassField(get_class($instance));
	}
}

?>