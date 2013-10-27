<?php

namespace eav;

class EavObject extends \ActiveRecordModel
{
	private $stringIdentifier;
	private $parent;

	public $ID;
	public $categoryID;//", "Category", "ID", null, ARInteger::instance()), false);
	public $customerOrderID;//", "CustomerOrder", "ID", null, ARInteger::instance()), false);
	public $manufacturerID;//", "Manufacturer", "ID", null, ARInteger::instance()), false);
	public $userID;
	public $userAddressID;//", "UserAddress", "ID", null, ARInteger::instance()), false);
	public $userGroupID;//", "UserGroup", "ID", null, ARInteger::instance()), false);
	public $transactionID;//", "Transaction", "ID", null, ARInteger::instance()), false);
	public $shippingServiceID;//", "ShippingService", "ID", null, ARInteger::instance()), false);
	public $staticPageID;//", "StaticPage", "ID", null, ARInteger::instance()), false);

	public $classID;
	
	public function initialize()
	{
		$this->belongsTo('productID', 'product\Product', 'ID', array('alias' => 'Product'));
		$this->belongsTo('userID', 'user\User', 'ID', array('alias' => 'User'));
		
        $this->hasMany('ID', 'eav\EavObjectValue', 'objectID', array(
            'alias' => 'EavObjectValue',
            'foreignKey' => array(
                'action' => \Phalcon\Mvc\Model\Relation::ACTION_CASCADE
            )
        ));

        $this->hasMany('ID', 'eav\EavItem', 'objectID', array(
            'alias' => 'EavItem',
            'foreignKey' => array(
                'action' => \Phalcon\Mvc\Model\Relation::ACTION_CASCADE
            )
        ));
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

		if ($parent->eavObjectID)
		{
			$eavObject = EavObject::getInstanceByID($parent->eavObjectID);
			$eavObject->classID = EavField::getClassID(get_class($parent));
			return $eavObject;
		}
		else
		{
			return self::getNewInstance($parent);
		}
	}
	
	public function get_Object()
	{
		return $this;
	}

	public static function getNewInstance(EavAble $parent)
	{
		if (!$parent->getID())
		{
			return null;
		}
		
		$field = self::getInstanceField($parent);

		$instance = new self();
		$instance->$field = $parent->getID();
		$instance->classID = EavField::getClassID($parent);
		$instance->parent = $parent;
		$parent->set_EavObject($instance);

		return $instance;
	}

	public static function getInstanceByIdentifier($stringIdentifier)
	{
		$instance = new self();
		$instance->classID = 0;
		$instance->setStringIdentifier($stringIdentifier);
		return $instance;
	}

	public function getClassField($className)
	{
		$parts = explode('\\', $className);
		$className = array_pop($parts);
		return lcfirst($className) . 'ID';
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

	private function getInstanceField(\ActiveRecordModel $instance)
	{
		return self::getClassField(get_class($instance));
	}
}

?>
