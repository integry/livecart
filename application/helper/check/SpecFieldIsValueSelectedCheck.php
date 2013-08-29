<?php


/**
 * Checks if a value has been selected or entered for a specField in product form
 *
 * @package application/helper/check
 * @author Integry Systems
 */
class SpecFieldIsValueSelectedCheck extends Check
{
	/**
	 * Specification field active record
	 *
	 * @var SpecField
	 */
	var $specField;

	/**
	 * Request object
	 *
	 * @var Request
	 */
	var $request;

	public function __construct($errorMessage, EavFieldCommon $specField, Request $request)
	{
		parent::__construct($errorMessage);
		$this->specField = $specField;
		$this->request = $request;
	}

	public function isValid($value)
	{
		if ($this->specField->isMultiValue)
		{
			$other = $this->request->gget('other');

			if(isset($other[$this->specField->getID()][0]) && "" != $other[$this->specField->getID()][0])
			{
				return true;
			}

			foreach($this->specField->getValuesSet() as $value)
			{
				if($this->request->isValueSet("specItem_" . $value->getID()))
				{
					return true;
				}
			}
		}

		else if ($this->request->isValueSet($this->specField->getFormFieldName()))
		{
			return true;
		}

		else if ('other' == $value)
		{
			$other = $this->request->gget('other');
			return !empty($other[$this->specField->getID()]);
		}

	  	return false;
	}
}

?>