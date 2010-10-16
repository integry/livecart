<?php

ClassLoader::import("application.model.ActiveRecordModel");

/**
 * BackendToolbarItem - buttons, last viewed
 *
 * @author Integry Systems <http://integry.com>
 */

class BackendToolbarItem extends ActiveRecordModel
{
	const TYPE_MENU = 1;
	const TYPE_PRODUCT = 2;
	const TYPE_USER = 3;
	const TYPE_ORDER = 4;

	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);

		$schema->setName(__CLASS__);
		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARField("ownerID", ARInteger::instance()));
		$schema->registerField(new ARField("menuID", ARVarchar::instance(16)));
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
		foreach(array('menuID', 'productID', 'userID', 'orderID', 'position') as $fieldName)
		{
			if (array_key_exists($fieldName, $data))
			{
				$item->$fieldName->set($data[$fieldName]);
			}
		}
		return $item;
	}

	public static function getUserToolbarItems($types=null, $filter=null)
	{
		if ($filter == null)
		{
			$filter = new ARSelectFilter();
		}
		$filter->mergeCondition(eq(f(__CLASS__.'.ownerID'), SessionUser::getUser()->getID()));
		$filter->setOrder(f(__CLASS__.'.position'), 'ASC');

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
		return self::getRecordSetArray(__CLASS__, $filter);
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
				BackendToolbarItem::getNewInstance($item)->save();
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


	//
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
		}
		return $itemArray;
	}
}

?>