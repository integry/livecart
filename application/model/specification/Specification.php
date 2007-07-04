<?php

ClassLoader::import('application.model.ActiveRecordModel');
ClassLoader::import('application.model.category.SpecField');

abstract class Specification extends ActiveRecordModel implements iSpecification
{
	public function getSpecField()
	{
		return $this->specField->get();
	}
	
	public function set($value)
	{
		$this->value->set($value);
	}	
}

?>