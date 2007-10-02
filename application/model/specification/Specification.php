<?php

ClassLoader::import('application.model.ActiveRecordModel');
ClassLoader::import('application.model.category.SpecField');

include_once(dirname(__file__) . '/iSpecification.php');

/**
 *
 * @package application.model.specification
 * @author Integry Systems <http://integry.com>   
 */
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