<?php

include_once dirname(__file__) . '/iSpecification.php';

/**
 * Links multiple pre-defined attribute values (of the same attribute) to a product
 *
 * @package application.model.specification
 * @author Integry Systems <http://integry.com>
 */
class MultiValueSpecificationItem implements iSpecification
{
	protected $items = array();

	protected $removedItems = array();

	protected $productInstance = null;

	protected $specFieldInstance = null;

	public static function getNewInstance(Product $product, SpecField $field, $value = false)
	{
		$specItem = new MultiValueSpecificationItem($product, $field);

		if ($value)
		{
			$specItem->set($value);
		}

		return $specItem;
	}

	public static function restoreInstance(Product $product, SpecField $field, $specValues)
	{
		$specItem = new MultiValueSpecificationItem($product, $field);

		if (is_array($specValues))
		{
			foreach ($specValues as $id => $value)
			{
				$specFieldValue = SpecFieldValue::restoreInstance($field, $id, $value);
				$item = SpecificationItem::restoreInstance($product, $field, $specFieldValue);
				$specItem->setItem($item);
			}
		}

		return $specItem;
	}

	protected function __construct(Product $product, SpecField $field)
	{
		$this->productInstance = $product;
		$this->specFieldInstance = $field;
	}

	public function set(SpecFieldValue $value)
	{
	  	// test whether the value belongs to the same field
		if ($value->specField->get()->getID() != $this->specFieldInstance->getID())
	  	{
			throw new Exception('Cannot assign SpecField:' . $value->specField->get()->getID() . ' value to SpecField:' . $this->specFieldInstance->getID());
		}

		if (!isset($this->items[$value->getID()]))
		{
		  	$item = SpecificationItem::getNewInstance($this->productInstance, $this->specFieldInstance, $value);
			$this->items[$value->getID()] = $item;
		  	unset($this->removedItems[$value->getID()]);
		}
	}

	public function removeValue(SpecFieldValue $value)
	{
	  	if (!isset($this->items[$value->getID()]))
	  	{
			return;
		}

		$this->removedItems[$value->getID()] = $this->items[$value->getID()];
	  	unset($this->items[$value->getID()]);
	}

	public function getSpecField()
	{
		return $this->specFieldInstance;
	}

	protected function setItem(SpecificationItem $item)
	{
		$this->items[$item->specFieldValue->get()->getID()] = $item;
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

	public function toArray()
	{
	  	$ret = array();

	  	$ret['SpecField'] = $this->specFieldInstance->toArray();

		// get value ID's
		$ids = array();
		$values = array();
		$isLanguage = (SpecField::TYPE_TEXT_SELECTOR == $this->specFieldInstance->type->get());

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

		unset($this->removedItems);
		unset($this->items);
		unset($this->productInstance);
		unset($this->specFieldInstance);
	}
}

?>