<?php

interface FilterInterface
{
	/**
	 * Create an ActiveRecord Condition object to use for product selection
	 *
	 * @return Condition
	 */
	public function getCondition();
	
	/**
     *	Adds JOIN definition to ARSelectFilter to retrieve product attribute value for the particular Filter
     *	
     *	@param	ARSelectFilter	$filter	Filter instance
     */
	public function defineJoin(ARSelectFilter $filter);	
}

?>