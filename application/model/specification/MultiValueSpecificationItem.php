<?php

class MultiValueSpecificationItem implements iSpecification
{
	protected $items = array();
	
	protected $productInstance = null;
	
	protected $specFieldInstance = null;
	
	protected function __construct(Product $product, SpecField $field)
	{
		$this->productInstance = $product;	  	
		$this->specFieldInstance = $field;	  	
	}
	
	public function setValue(SpecFieldValue $value)
	{
	  	$item = SpecificationItem::getNewInstance($this->productInstance, $this->specFieldInstance, $value);
		$this->items[$value->getID()] = $item;
	  	unset($this->removedValues[$value->getID()]);
	}
	
	public function removeValue(SpecFieldValue $value)
	{
	  	unset($this->values[$value->getID()]);
	  	$this->removedValues[$value->getID()] = true;
	}
	
	public function save()
	{
	  	foreach ($this->items as $item)
	  	{
		    $item->save();
		}
	}

	public function getSpecField()
	{
		return $this->specFieldInstance;  
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
	
}

?>