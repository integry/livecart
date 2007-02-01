<?php

ClassLoader::import('application.model.ActiveRecordModel');

abstract class Specification extends ActiveRecordModel implements iSpecification
{
	protected $productInstance;
	
/*	protected $specFieldInstance; */
	
	public function getSpecField()
	{
	  	echo '<font color=green>' . get_class($this->specField) . ' / ' . get_class($this) .'</font><br>';
		return $this->specField->get();
	}
	
	public function setValue($value)
	{
	  	$this->value->set($value);
	}	
}

?>