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
	
	private $includeSubcategories = false;

	public function __construct(Category $category, ARSelectFilter $filter)
	{
		$this->category = $category;
		$this->selectFilter = $this->category->getProductFilter($filter);
	}
	
	/**
	 * Applies a filter to a product set
	 *
	 * @param Filter $filter
	 */
	public function applyFilter(FilterInterface $filter)
	{
		$this->filters[] = $filter;
	}
	
	public function getFilters()
	{
		return $this->filters;
	}

	public function getFilterCount()
	{
		return count($this->filters);
	}

	public function getSelectFilter()
	{
		$selectFilter = new ARSelectFilter();
		$selectFilter->merge($this->selectFilter);

		$cond = $selectFilter->getCondition();

		foreach ($this->filters as $filter)
		{
			if (!$cond)
			{
			  	$cond = $filter->getCondition();
			}		  
			else
			{
			  	$filterCond = $filter->getCondition();
			  	if ($filterCond)
			  	{
					$cond->addAND($filterCond);					
				}
			}
		
			$filter->defineJoin($selectFilter);		
		}

		if ($cond)
		{
			$selectFilter->setCondition($cond);		  
		}

	  	return $selectFilter;
	}
	
	public function orderByPrice(Currency $currency, $direction = 'ASC')
	{
	  	if ('ASC' != $direction)
	  	{
		    $direction = 'DESC';
		}
		
		$currency->defineProductJoin($this->selectFilter);
		$this->selectFilter->setOrder(new ARFieldHandle($currency->getJoinAlias(), 'price'), $direction);
	}
	
	public function getCategory()
	{
	  	return $this->category;
	}
	
	public function includeSubcategories()
	{
        $this->includeSubcategories = true;
    }
    
    public function isSubcategories()
    {
        return $this->includeSubcategories;
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