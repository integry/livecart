<?php

ClassLoader::import('application.model.filter.SpecificationFilterInterface');

class SelectorFilter implements SpecificationFilterInterface
{
	private $specFieldValue;
	
	public function __construct(SpecFieldValue $specFieldValue)
	{
		$this->specFieldValue = $specFieldValue;
	}
	
	public function getCondition()
	{
		return new EqualsCond(new ARExpressionHandle($this->getJoinAlias() . '.SpecFieldValueID'), $this->specFieldValue->getID());	
	}
	
	/**
     *	Adds JOIN definition to ARSelectFilter to retrieve product attribute value for the particular SpecField
     *	
     *	@param	ARSelectFilter	$filter	Filter instance
     */
	public function defineJoin(ARSelectFilter $filter)
    {
		$table = $this->getJoinAlias();
		$filter->joinTable('SpecificationItem', 'Product', 'productID AND ' . $table . '.SpecFieldValueID = ' . $this->specFieldValue->getID(), 'ID', $table);				  				  		  	
	}
	
	public function getID()
	{
		return $this->specFieldValue->specField->get()->getID() . '_' . $this->specFieldValue->getID();
	}
	
	public function toArray()
	{
		$array = $this->specFieldValue->toArray();
		$array['name_lang'] = $array['value_lang'];
		$array['handle'] = Store::createHandleString($array['value_lang']);
		$array['ID'] = 'v' . $array['ID'];
		return $array;
	}
	
	public function getSpecField()
	{
		return $this->specFieldValue->specField->get();
	}
	
	protected function getJoinAlias()
	{
		return 'specFieldValue_' . $this->specFieldValue->getID();	  			 	  	
	}	
}

?>