<?php


class EavObject extends ActiveRecordModel
{
	private $stringIdentifier;
	private $parent;

	public static function defineSchema($className = __CLASS__)
	{


		public $ID;
		public $categoryID", "Category", "ID", null, ARInteger::instance()), false);
		public $customerOrderID", "CustomerOrder", "ID", null, ARInteger::instance()), false);
		public $manufacturerID", "Manufacturer", "ID", null, ARInteger::instance()), false);
		public $userID", "User", "ID", null, ARInteger::instance()), false);
		public $userAddressID", "UserAddress", "ID", null, ARInteger::instance()), false);
		public $userGroupID", "UserGroup", "ID", null, ARInteger::instance()), false);
		public $transactionID", "Transaction", "ID", null, ARInteger::instance()), false);
		public $shippingServiceID", "ShippingService", "ID", null, ARInteger::instance()), false);
		public $staticPageID", "StaticPage", "ID", null, ARInteger::instance()), false);

		public $classID;
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

		if ($parent->eavObject)
		{
			$parent->eavObject->classID = EavField::getClassID(get_class($parent)));
			return $parent->eavObject;
		}
		else
		{
			return self::getNewInstance($parent);
		}
	}

	public static function getNewInstance(EavAble $parent)
	{
		$field = self::getInstanceField($parent);
		$instance = new __CLASS__();
		$instance->$field = $parent;
		$instance->classID = EavField::getClassID($parent));

		$instance->parent = $parent;
		$parent->eavObject = $instance;

		return $instance;
	}

	public static function getInstanceByIdentifier($stringIdentifier)
	{
		$instance = new __CLASS__();
		$instance->classID = 0);
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
			if ($this->data[$key])
			{
				return $this->data[$key];
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