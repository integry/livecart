<?php

ClassLoader::import("application.model.ActiveRecordModel");

/**
 * BackendToolbarItem - buttons, last viewed
 *
 * @author Integry Systems <http://integry.com>
 */

class BackendToolbarItem extends ActiveRecordModel
{
	const LAST_VIEWED_COUNT = 28; //how many items keep for last viewed menu
	const TYPE_MENU = 1;
	const TYPE_PRODUCT = 2;
	const TYPE_USER = 3;
	const TYPE_ORDER = 4;

	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName(__CLASS__);
		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("ownerID", "User", "ID", "User", ARInteger::instance()));
		$schema->registerField(new ARField("menuID", ARVarchar::instance(16)));
		$schema->registerField(new ARForeignKeyField("productID", "Product", "ID", "Product", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("userID", "User", "ID", "User", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("orderID", "CustomerOrder", "ID", "CustomerOrder", ARInteger::instance()));
		$schema->registerField(new ARField("position", ARInteger::instance()));

	}

	// BackendToolbarItem::registerLastViewedOrder($order);
	public static function registerLastViewedOrder(CustomerOrder $order)
	{
		self::registerLastViewed(array('orderID' => $order->getID(), 'instance'=>$order));
	}

	// BackendToolbarItem::registerLastViewedUser($user);
	public static function registerLastViewedUser(User $user)
	{
		self::registerLastViewed(array('userID' => $user->getID(), 'instance'=>$user));
	}

	// BackendToolbarItem::registerLastViewedProduct($product);
	public static function registerLastViewedProduct(Product $product)
	{
		self::registerLastViewed(array('productID' => $product->getID(), 'instance'=>$product));
		$schema->registerField(new ARField("productID", ARInteger::instance()));
		$schema->registerField(new ARField("userID", ARInteger::instance()));
		$schema->registerField(new ARField("orderID", ARInteger::instance()));
		$schema->registerField(new ARField("position", ARInteger::instance()));
		/*
		 * TODO: do i need to define product, user, order ids as ARForeignKeyField()?
		 */
	}

	public static function getNewInstance($data)
	{
		$item = new BackendToolbarItem();

		$item->ownerID->set(SessionUser::getUser()->getID());
		foreach(array('productID', 'userID', 'orderID') as $fieldName)
		{
			if (array_key_exists($fieldName, $data))
			{
				$item->$fieldName->set($data['instance']);
				break; // should have only one instance;
			}
		}

		foreach(array('menuID', 'position') as $fieldName)
		{
			if (array_key_exists($fieldName, $data))
			{
				$item->$fieldName->set($data[$fieldName]);
			}
		}

		return $item;
	}

	public static function getUserToolbarItems($types=null, $filter=null, $order='ASC')
	{
		if ($filter == null)
		{
			$filter = new ARSelectFilter();
		}
		$filter->mergeCondition(eq(f(__CLASS__.'.ownerID'), SessionUser::getUser()->getID()));
		$filter->setOrder(f(__CLASS__.'.position'), $order);

		$m = array(
			BackendToolbarItem::TYPE_MENU =>'menuID',
			BackendToolbarItem::TYPE_PRODUCT =>'productID',
			BackendToolbarItem::TYPE_USER => 'userID',
			BackendToolbarItem::TYPE_ORDER => 'orderID'
		);

		if (is_array($types) == false)
		{
			$types = array($types);
		}
		$conditions = array();
		foreach ($types as $type)
		{
			if (array_key_exists($type, $m))
			{
				$conditions[] = isnotnull(f(__CLASS__.'.'.$m[$type]));

			}
		}
		if (count($conditions))
		{
			$filter->mergeCondition(new OrChainCondition($conditions) );
		}
		return self::getRecordSetArray(__CLASS__, $filter, true);
	}

	public static function saveItemArray($array)
	{
		// update position
		$position = 0;
		foreach($array as &$item)
		{
			$item['position'] = $position;
			$position++;

			// update existing or insert new
			if (array_key_exists('ID',$item))
			{
				$update = new ARUpdateFilter();
				$update->setCondition(new EqualsCond(new ARFieldHandle(__CLASS__, 'ID'), $item['ID']));
				$update->addModifier('position', $item['position']);
				ActiveRecord::updateRecordSet(__CLASS__, $update);
			}
			else
			{
				self::getNewInstance($item)->save();
			}
		}

		return true;
	}

	public static function deleteMenuItem($menuID, $position=null)
	{
		$filter = select(eq(f(__CLASS__.'.menuID'), $menuID));
		if ($position !== null)
		{
			$filter->mergeCondition(eq(f(__CLASS__.'.position'), $position));
		}
		$items = self::getUserToolbarItems(BackendToolbarItem::TYPE_MENU, $filter);
		if (count($items) == 1)
		{
			ActiveRecord::deleteByID(__CLASS__, $items[0]['ID']);
			return true;
		}
		return false;
	}

	public static function sanitizeItemArray($itemArray)
	{
		foreach ($itemArray as &$item)
		{
			unset($item['__class__']);
			foreach(array('menuID', 'productID', 'userID', 'orderID') as $fieldName)
			{
				if (array_key_exists($fieldName, $item) && $item[$fieldName] !== null)
				{
					$item['type'] = str_replace('ID', '',$fieldName);
				}
				else
				{
					unset($item[$fieldName]);
				}
			}
			foreach(array('Product', 'User', 'CustomerOrder') as $fieldName)
			{
				if (array_key_exists($fieldName, $item) && $item[$fieldName]['ID'] === null)
				{
					unset($item[$fieldName]);
				}
			}
		}

		return $itemArray;
	}

	public static function registerLastViewed($item)
	{
		$item['position'] = time();
		$filter = new ARSelectFilter();
		foreach(array('menuID', 'productID', 'userID', 'orderID') as $fieldName)
		{
			if (array_key_exists($fieldName, $item))
			{
				$filter->setCondition(eq(f(__CLASS__.'.'.$fieldName), $item[$fieldName]));
				break; // should have only one identificator.
			}
		}
		$items = self::getUserToolbarItems(null, $filter);

		if (count($items) > 0)
		{
			// update postion to $item['position'] for first found existing record
			$update = new ARUpdateFilter();
			$update->setCondition(new EqualsCond(new ARFieldHandle(__CLASS__, 'ID'), $items[0]['ID']));
			$update->addModifier('position', $item['position']);
			ActiveRecord::updateRecordSet(__CLASS__, $update);
		}
		else
		{
			// create new
			self::getNewInstance($item)->save();
		}

		$filter = new ARSelectFilter();
		$filter->setLimit(999, BackendToolbarItem::LAST_VIEWED_COUNT);
		$items = self::getUserToolbarItems(
			array(BackendToolbarItem::TYPE_PRODUCT, BackendToolbarItem::TYPE_USER, BackendToolbarItem::TYPE_ORDER),
			$filter,
			'DESC'
		);
		if (count($items) > 0)
		{
			foreach($items as $item)
			{
				ActiveRecord::deleteByID(__CLASS__, $item['ID']);
			}
		}
		return true;
	}
}

?>
