<?php

ClassLoader::import('application.model.ActiveRecordModel');

abstract class Specification extends ActiveRecordModel
{
	protected $productInstance;
	
/*	protected $specFieldInstance; */
	
	public function getSpecField()
	{
	  	return $this->specField->get();
	}
	
	public function setValue($value)
	{
	  	$this->value->set($value);
	}
}

?>