<?php

/**
 * Defines common interface for different types of filters.
 * It makes it possible to filter products not only by their attribute values, but also by price, manufacturer,
 * keyword search, etc.
 *
 * @package application.model.filter
 * @author Integry Systems <http://integry.com>
 */
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