<?php

/**
 *
 * @package application.model.product
 *
 */
class ProductFilter
{
	private $category = null;
	private $selectFilter = null;
	private $condition = null;	

	private $filters = array();

	public function __construct(Category $category, ARSelectFilter $filter)
	{
		$this->category = $category;
		$this->selectFilter = $filter;
	}
	
	/**
	 * Applies a filter to a product set
	 *
	 * @param Filter $filter
	 */
	public function applyFilter(Filter $filter)
	{
		$this->addCondition($filter->getCondition());
	}

	public function getSelectFilter()
	{
	  	if ($this->condition)
	  	{
			$this->selectFilter->setCondition($this->condition);		    
		}

	  	return $this->selectFilter;
	}
	
	public function getCategory()
	{
	  	return $this->category;
	}
	
	protected function addCondition(Condition $cond)
	{
	  	if (!$this->condition)
	  	{
			$this->condition = $cond;    
		}
		else
		{
			$this->condition->addAND($cond);  	
		}
	}
}

?>