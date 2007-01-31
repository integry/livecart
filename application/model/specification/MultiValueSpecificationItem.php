<?php

class MultiValueSpecificationItem extends Specification
{
	protected $items = array();
	
	public function addItem(SpecificationItem $item)
	{
	  	$this->items[] = $item;
	}
	
	public function save()
	{
	  	foreach ($this->items as $item)
	  	{
		    $item->save();
		}
	}
}

?>