<?php

class MultiValueSpecificationItem implements iSpecification
{
	protected $items = array();
	
	protected $removedItems = array();
	
	protected $productInstance = null;
	
	protected $specFieldInstance = null;
	
	protected function __construct(Product $product, SpecField $field)
	{
		$this->productInstance = $product;	  	
		$this->specFieldInstance = $field;	  	
	}
	
	public function setValue(SpecFieldValue $value)
	{
	  	// test whether the value belongs to the same field
		if ($value->specField->get()->getID() != $this->specFieldInstance->getID())
	  	{
		    throw new Exception('Cannot assign SpecField:' . $value->specField->get()->getID() . ' value to SpecField:' . $this->specFieldInstance->getID());
		}

	  	$item = SpecificationItem::getNewInstance($this->productInstance, $this->specFieldInstance, $value);
		$this->items[$value->getID()] = $item;
	  	unset($this->removedItems[$value->getID()]);
	}
	
	public function removeValue(SpecFieldValue $value)
	{
	  	$this->removedItems[$value->getID()] = $this->items[$value->getID()];
	  	unset($this->items[$value->getID()]);
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

	public function getSpecField()
	{
		return $this->specFieldInstance;  
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
		  	
			$value = $item->specFieldValue->get()->toArray(ActiveRecordModel::NON_RECURSIVE);
			
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
	
	public static function getNewInstance(Product $product, SpecField $field, $value = false)
	{
		$specItem = new MultiValueSpecificationItem($product, $field);
		
		if ($value)
		{
			$specItem->setValue($value); 	  	
		}		
		
		return $specItem;
	}
	
	protected function deleteRemovedValues()
	{
	  	foreach ($this->removedItems as $item)
	  	{
		    $item->delete();
		}
		
		$this->removedItems = array();
	}
	
}

?>