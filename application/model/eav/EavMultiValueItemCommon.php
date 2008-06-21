<?php

ClassLoader::import('application.model.eav.iEavSpecification');

/**
 * Links multiple pre-defined attribute values (of the same attribute) to a product
 *
 * @package application.model.specification
 * @author Integry Systems <http://integry.com>
 */
abstract class EavMultiValueItemCommon implements iEavSpecification
{
	protected $items = array();

	protected $removedItems = array();

	protected $ownerInstance = null;

	protected $fieldInstance = null;

	public abstract function getItemClassName();

	public static function getNewInstance($className, ActiveRecordModel $owner, EavFieldCommon $field, $value = false)
	{
		$specItem = new $className($owner, $field);

		if ($value)
		{
			$specItem->set($value);
		}

		return $specItem;
	}

	public static function restoreInstance($className, ActiveRecordModel $owner, EavFieldCommon $field, $specValues)
	{
		$specItem = new $className($owner, $field);

		$itemClass = call_user_func(array($className, 'getItemClassName'));
		$valueClass = call_user_func(array($itemClass, 'getValueClass'));

		if (is_array($specValues))
		{
			foreach ($specValues as $id => $value)
			{
				$specFieldValue = call_user_func_array(array($valueClass, 'restoreInstance'), array($field, $id, $value));
				$item = call_user_func_array(array($itemClass, 'restoreInstance'), array($owner, $field, $specFieldValue));
				$specItem->setItem($item);
			}
		}

		return $specItem;
	}

	protected function __construct(ActiveRecordModel $owner, EavFieldCommon $field)
	{
		$this->ownerInstance = $owner;
		$this->fieldInstance = $field;
	}

	public function set(EavValueCommon $value)
	{
	  	// test whether the value belongs to the same field
		if ($value->getField()->get()->getID() != $this->fieldInstance->getID())
	  	{
			$class = get_class($value->getField()->get());
			throw new Exception('Cannot assign ' . $class . ':' . $value->getField()->get()->getID() . ' value to ' . $class . ':' . $this->fieldInstance->getID());
		}

		if (!isset($this->items[$value->getID()]))
		{
		  	$item = call_user_func_array(array($this->getItemClassName(), 'getNewInstance'), array($this->ownerInstance, $this->fieldInstance, $value));
			$this->items[$value->getID()] = $item;
		  	unset($this->removedItems[$value->getID()]);
		}
	}

	public function removeValue(EavValueCommon $value)
	{
	  	if (!isset($this->items[$value->getID()]))
	  	{
			return;
		}

		$this->removedItems[$value->getID()] = $this->items[$value->getID()];
	  	unset($this->items[$value->getID()]);
	}

	public function getField()
	{
		return null;
	}

	public function getFieldInstance()
	{
		return $this->fieldInstance;
	}

	protected function setItem(EavItemCommon $item)
	{
		$this->items[$item->getValue()->get()->getID()] = $item;
	}

	protected function deleteRemovedValues()
	{
	  	foreach ($this->removedItems as $item)
	  	{
			$item->delete();
		}

		$this->removedItems = array();
	}

	public function save()
	{
	  	$this->deleteRemovedValues();

		foreach ($this->items as $item)
	  	{
			if ($item->isModified())
			{
				$item->save();
			}
		}
	}

	public function delete()
	{
	  	$this->deleteRemovedValues();

		foreach ($this->items as $key => $item)
	  	{
			$item->delete();
			unset($this->items[$key]);
		}
	}

	public function __destruct()
	{
		foreach ($this->items as $k => $attr)
		{
			$this->items[$k]->__destruct();
		}

		foreach ($this->removedItems as $k => $attr)
		{
			$this->removedItems[$k]->__destruct();
		}

		$this->removedItems = array();
		$this->items = array();
		unset($this->ownerInstance);
		unset($this->fieldInstance);
	}

	public function toArray()
	{
	  	$ret = array();

	  	$ret[call_user_func(array($this->getItemClassName(), 'getFieldClass'))] = $this->fieldInstance->toArray();

		// get value ID's
		$ids = array();
		$values = array();
		$isLanguage = (EavFieldCommon::TYPE_TEXT_SELECTOR == $this->fieldInstance->type->get());

		foreach ($this->items as $id => $item)
		{
		  	$ids[] = $id;

			$value = $item->specFieldValue->get()->toArray();

			if ($isLanguage)
			{
			  	$v = array();
				foreach ($value as $key => $val)
			  	{
					if (substr($key, 0, 5) == 'value')
					{
						$v[$key] = $val;
					}
				}
			}
			else
			{
			  	$v = $value['value'];
			}

			$values[] = $v;
		}

		$ret['valueIDs'] = $ids;
		$ret['values'] = $values;

		return $ret;
	}
}

?>