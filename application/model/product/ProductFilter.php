<?php

ClassLoader::import('application.model.product.Product');

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

	public function getSelectFilter($disableFilters = false)
	{
		$selectFilter = $this->category->getProductsFilter($this, false);
		$selectFilter->merge($this->productFilter);
		$cond = null;
		if ($disableFilters == false)
		{
			$list = array();
			// group filters by class
			foreach ($this->filters as $filter)
			{
				$id = ($filter instanceof SpecificationFilterInterface) ? $filter->getFilterGroup()->getID() : '';
				$list[get_class($filter) . '_' . $id][] = $filter->getCondition();
				$filter->defineJoin($selectFilter);
			}
			// convert filter group to OrChainCondition
			foreach ($list as &$filterGroup)
			{
				$filterGroup = new OrChainCondition($filterGroup);
			}
			if ($fCond = $selectFilter->getCondition())
			{
				$list[] = $fCond;
			}
			$selectFilter->setCondition(new AndChainCondition($list)); // all merged with and
		}
		ActiveRecordModel::getApplication()->processInstancePlugins('finalProductFilter', $selectFilter);
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

	public function setEnabledOnly()
	{
		$this->productFilter->mergeCondition(new EqualsCond(new ARFieldHandle('Product', 'isEnabled'), true));
	}

	public function isSubcategories()
	{
		return $this->includeSubcategories;
	}

	public function setFilters($filters)
	{
		$this->filters = $filters;
	}
}

?>