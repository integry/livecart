<?php

namespace eav;

/**
 * Links multiple pre-defined attribute values (of the same attribute) to a product
 *
 * @package application/model/specification
 * @author Integry Systems <http://integry.com>
 */
class EavMultiValueItem implements iEavSpecification
{
	protected $items = array();

	protected $removedItems = array();

	protected $ownerInstance = null;

	protected $fieldInstance = null;
	
	protected $manager = null;
	
	public function setManager(EavSpecificationManager $manager)
	{
		$this->manager = $manager;
	}

	public static function getNewInstance(EavSpecificationManager $manager, EavField $field, $value = false)
	{
		$specItem = new EavMultiValueItem($manager, $field);

		if ($value)
		{
			$specItem->setValue($value);
		}

		return $specItem;
	}
	
	public function getField()
	{
		return $this->fieldInstance;
	}

	protected function __construct(EavSpecificationManager $manager, EavField $field)
	{
		$this->setManager($manager);
		$this->ownerInstance = $manager->getOwner();
		$this->fieldInstance = $field;
	}

	public function setOwner(EavObject $object)
	{
		foreach ($this->items as $item)
		{
			$item->setOwner($object);
			$this->ownerInstance = $object;
		}
	}

	public function setValue(EavValue $value)
	{
	  	// test whether the value belongs to the same field
		if ($value->get_Field()->getID() != $this->fieldInstance->getID())
	  	{
			//$class = get_class($value->get_Field());
			//throw new Exception('Cannot assign ' . $class . ':' . $value->get_Field()->getID() . ' value to ' . $class . ':' . $this->fieldInstance->getID());
		}

		if (!isset($this->items[$value->getID()]))
		{
			$item = EavItem::getNewInstance($this->ownerInstance, $this->fieldInstance, $value);
			$item->setManager($this->manager);
			$this->items[$value->getID()] = $item;
		  	unset($this->removedItems[$value->getID()]);
		}
	}

	public function removeValue(EavValue $value)
	{
	  	if (!isset($this->items[$value->getID()]))
	  	{
			return;
		}

		$this->removedItems[$value->getID()] = $this->items[$value->getID()];
	  	unset($this->items[$value->getID()]);
	}

	public function getFieldInstance()
	{
		return $this->fieldInstance;
	}

	public function setItem(EavItem $item)
	{
		$this->items[$item->valueID] = $item;
	}

	protected function deleteRemovedValues()
	{
	  	foreach ($this->removedItems as $item)
	  	{
			$item->delete();
		}

		$this->removedItems = array();
	}
	
	public function getItems()
	{
		return $this->items;
	}

	public function save()
	{
		$this->deleteRemovedValues();

		foreach ($this->items as $item)
		{
			$item->save();
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

	public function toArray()
	{
	  	$ret = array();

	  	$ret['EavField'] = $this->fieldInstance->toArray();

		// get value ID's
		$ids = array();
		$values = array();
		$isLanguage = (EavField::TYPE_TEXT_SELECTOR == $this->fieldInstance->type);

		foreach ($this->items as $id => $item)
		{
			$ids[] = $id;

			$value = $item->get_Value()->toArray();

			if (0 && $isLanguage)
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
	
	public function getFormattedValue($options)
	{
		$values = array();
		foreach ($this->items as $item)
		{
			$values[] = $item->getFormattedValue();
		}
		
		if (!empty($options['separator']))
		{
			return implode($options['separator'], $values);
		}
		
		return $values;
	}
	
	public function getRawValue()
	{
		$ret = array();
		foreach ($this->items as $item)
		{
			$ret[] = $item->getRawValue();
		}
		
		return $ret;
	}
	
	public function replaceValue(EavMultiValueItem $newValue)
	{
		$newItems = $newValue->getItems();
		foreach ($this->items as $key => $item)
		{
			if (!isset($newItems[$key]))
			{
				$this->removedAttributes[$key] = $item;
				unset($this->items[$key]);
			}
			else
			{
				unset($this->removedAttributes[$key]);
			}
		}
		
		$this->items = $newItems;
	}
}

?>
