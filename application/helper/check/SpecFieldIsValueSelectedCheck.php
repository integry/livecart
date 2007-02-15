<?php

ClassLoader::import("framework.request.validator.check.Check");

/**
 * Checks if a value has been selected or entered for a specField in product form
 *
 * @package application.helper.check
 */
class SpecFieldIsValueSelectedCheck extends Check
{
	var $specField;
	var $request;
		
	public function __construct($errorMessage, SpecField $specField, Request $request)
	{
		parent::__construct($errorMessage);
		$this->specField = $specField;  
		$this->request = $request;  
	}
	
	public function isValid($value)
	{
		if ($this->specField->isMultiValue->get())
		{
			  
		}
		else
		{
			if ('other' == $value)
			{
				$other = $this->request->getValue('other');
				return !empty($other[$this->specField->getID()]);	
			}	
			else
			{
			  	return true;
			} 
		}
	}
}

?>