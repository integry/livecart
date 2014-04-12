<?php

namespace product;

use category\Category;

/**
 * Category product filter. Generates ARSelectFilter object for selecting products
 * according to defined conditions (category, applied filters, etc)
 *
 * @package application/model/product
 * @author Integry Systems <http://integry.com>
 */
class ProductFilter extends \Phalcon\Mvc\Model\Query\Builder
{
	private $category = null;

	private $filters = array();
	
	private $sort;

	public function __construct($params = NULL)
	{
		parent::__construct($params);
		$this->join('category\Category', 'product\Product.categoryID=category\Category.ID', '', 'LEFT');
		$this->from('product\Product');
	}
	
	public function setCategory(Category $category, $includeSubcategories)
	{
		$this->category = $category;
		$this->category->setProductCondition($this, $includeSubcategories);
	}
	
	/**
	 * Applies a filter to a product set
	 *
	 * @param Filter $filter
	 */
	public function applyFilter(\filter\FilterInterface $filter, $params)
	{
		$filter->setCondition($this, $params);
	}

	public function getSelectFilter($disableFilters = false)
	{
		$selectFilter = $this->category->getProductsFilter($this, false);
		$selectFilter->merge($this->productFilter);
		$cond = null;

		$list = array();
		// group filters by class
		foreach ($this->filters as $filter)
		{
			if ($disableFilters && !($filter instanceof SearchFilter))
			{
				continue;
			}

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

		$selectFilter->orderBy(f('Product.ID'), 'DESC');

		ActiveRecordModel::getApplication()->processInstancePlugins('finalProductFilter', $selectFilter);
		return $selectFilter;
	}

	public function orderByPrice($direction = 'ASC')
	{
	  	if ('ASC' != $direction)
	  	{
			$direction = 'DESC';
		}
		
		$this->join('product\ProductPrice', '(product\ProductPrice.productID=product\Product.ID) AND (product\ProductPrice.currencyID = "' . $this->category->getDI()->get('application')->getDefaultCurrencyCode() . '")', '', 'LEFT');
		$this->orderBy('product\ProductPrice.price ' . $direction);
	}

	public function getCategory()
	{
	  	return $this->category;
	}

	public function setEnabledOnly()
	{
		$this->andWhere('product\Product.isEnabled = :isEnabled:', array('isEnabled' => true));
	}
	
	public function setSortOrder($request)
	{
		$sort = $request->getParam('sort');
		if (!$sort)
		{
			$sort = $this->getDefaultSortOrder();
		}
		
		switch (strtoupper($sort))
		{
			case 'SALES_RANK':
				$this->orderBy('product\Product.salesRank');
			break;

			case 'PRICE_ASC':
				$this->orderByPrice('ASC');
			break;

			case 'PRICE_DESC':
				$this->orderByPrice('DESC');
			break;
			
			case 'NEWEST_ARRIVALS':
				$this->orderBy('product\Product.ID DESC');
			break;
		}

		$this->sort = $sort;
	}
	
	public function getSortOrder()
	{
		return strtolower($this->sort);
	}
	
	public function isDefaultSortOrder()
	{
		return strtolower($this->getSortOrder()) == strtolower($this->getDefaultSortOrder());
	}

	public function getDefaultSortOrder()
	{
		return $this->category->getDI()->get('config')->get('SORT_ORDER');
	}
}

?>
