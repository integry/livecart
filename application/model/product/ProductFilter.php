<?php

/**
 * Category product filter. Generates ARSelectFilter object for selecting products
 * according to defined conditions (category, applied filters, etc)
 * 
 * @package application.model.product
 * @author Integry Systems <http://integry.com>   
 */
class ProductFilter
{
	private $category = null;

	private $selectFilter = null;

	private $productFilter;

	private $filters = array();
	
	private $includeSubcategories = false;

	public function __construct(Category $category, ARSelectFilter $filter)
	{
		$this->category = $category;
		$this->productFilter = $filter;
		$this->selectFilter = $this->category->getProductFilter($filter);
//		$this->selectFilter->mergeCondition(new EqualsCond(new ARFieldHandle('Product', 'isEnabled'), true));
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
	
	public function getSelectFilterInstance()
	{
		return $this->selectFilter;
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
		$this->selectFilter = $this->category->getProductsFilter($this);
    }
    
    public function isSubcategories()
    {
        return $this->includeSubcategories;
    }
}

?>