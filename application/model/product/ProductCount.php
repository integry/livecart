<?php


/**
 * Calculates the number of products found in categories by filters, manufacturers,
 * price intervals, search queries, etc.
 *
 * @package application/model/product
 * @author Integry Systems <http://integry.com>
 */
class ProductCount
{
	protected $productFilter;

	private $application;

	public function __construct(ProductFilter $productFilter, LiveCart $application)
	{
		$this->productFilter = $productFilter;
		$this->application = $application;
  	}

	public function getCountByFilters($includeAppliedFilters)
	{
		$filters = $this->productFilter->getCategory()->getFilterSet();

		// exclude already applied filters
		foreach ($this->productFilter->getFilters() as $appliedFilter)
		{
			foreach ($filters as $key => $filter)
			{
				// Selector filters
				if ($filter instanceof SelectorFilter && $appliedFilter instanceof SelectorFilter)
				{
					if ($filter->getID() == $appliedFilter->getID())
					{
						//unset($filters[$key]);
					}
				}

				// value range filters
				elseif ($filter instanceof Filter && $appliedFilter instanceof Filter)
				{
					if ($filter->filterGroup === $appliedFilter->filterGroup)
					{
						//unset($filters[$key]);
					}
				}
			}
		}

		return $this->getCountByFilterSet($filters, $includeAppliedFilters);
	}

	public function getCategoryProductCount()
	{
		$filter = $this->productFilter->getSelectFilter();
		$filter->joinTable('Category', 'Product', 'ID', 'categoryID');
		return ActiveRecordModel::getRecordCount('Product', $filter);
	}

	public function getCountByPrices($includeAppliedFilters)
	{
		// get price filters
		$k = 0;
		$filters = array();
		$config = $this->application->getConfig();
		while ($config->has('PRICE_FILTER_NAME_' . ++$k))
		{
			if ($config->get('PRICE_FILTER_NAME_' . $k) && !is_array($config->get('PRICE_FILTER_NAME_' . $k)))
			{
				$from = (int)$config->get('PRICE_FILTER_FROM_' . $k);
				$to = (int)$config->get('PRICE_FILTER_TO_' . $k);
				if ($to)
				{
					$filters[$k] = array($from, $to);
				}
			}
		}

		if (!$filters)
		{
			return array();
		}

		// get product counts
		$selectFilter = $this->productFilter->getSelectFilter(!$includeAppliedFilters);
		$selectFilter->removeFieldList();
		$selectFilter->limit(0);

		$query = new ARSelectQueryBuilder();
		$query->includeTable('Product');
		$query->joinTable('ProductPrice', 'Product', 'productID AND (ProductPrice.currencyID = "' . $this->application->getDefaultCurrencyCode() . '")', 'ID');
		$query->joinTable('Category', 'Product', 'ID', 'categoryID');

		foreach ($filters as $key => $filter)
		{
			$query->addField('SUM(ProductPrice.price >= ' . $filter[0] . ' AND ProductPrice.price <= ' . $filter[1] . ')', null, $key);
		}

		$query->setFilter($selectFilter);

		$data = ActiveRecordModel::getDataBySQL($query->getPreparedStatement(ActiveRecordModel::getDBConnection()));
		//$data = array_diff($data[0], array(0));
		return $data[0];
	}

	public function getCountByManufacturers($includeAppliedFilters)
	{
		$selectFilter = $this->productFilter->getSelectFilter(!$includeAppliedFilters);
		$selectFilter->removeFieldList();
		$selectFilter->limit(0);
		$selectFilter->orderBy(new ARExpressionHandle('cnt'), 'DESC');
		$selectFilter->setGrouping(new ARFieldHandle('Product', 'manufacturerID'));
		$selectFilter->mergeHavingCondition(new MoreThanCond(new ARExpressionHandle('cnt'), 0));
		$selectFilter->mergeHavingCondition(new NotEqualsCond(new ARFieldHandle('Manufacturer', 'name'), ''));
		$selectFilter->reorderBy();
		$selectFilter->orderBy(new ARFieldHandle('Manufacturer', 'name'));

		$query = new ARSelectQueryBuilder();
		$query->includeTable('Product');
		$query->joinTable('Manufacturer', 'Product', 'ID', 'manufacturerID');
		$query->joinTable('Category', 'Product', 'ID', 'categoryID');
		$query->addField('COUNT(Product.manufacturerID)', null, 'cnt');
		$query->addField('ID', 'Manufacturer');
		$query->addField('name', 'Manufacturer');
		$query->setFilter($selectFilter);

		ActiveRecordModel::getApplication()->processInstancePlugins('manufacturerCountQuery', $query);
		$data = ActiveRecordModel::getDataBySQL($query->getPreparedStatement(ActiveRecord::getDBConnection()));

		return $data;
	}

	public function getCountByFilterSet($filters, $includeAppliedFilters)
	{
		if (!$filters)
		{
			return array();
		}

		// slice the filters array in separate sets
		// MySQL only allows 31 or 61 joins in a single query (depending on whether it's a 32 or 64 bit system)
		// so sometimes the counts cannot be retrieved via single query if there are many filters
		$filterSets = array();
		for ($k = 0; $k < count($filters); $k+=30)
		{
			$filterSets[] = array_slice($filters, $k, 30);
		}

		$ret = array();
		foreach ($filterSets as $set)
		{
			$cnt = $this->getCountByFilterSubSet($set, $includeAppliedFilters);

			// array_merge would reindex the numeric keys
			foreach ($cnt as $key => $value)
			{
				$ret[$key] = $value;
			}
		}

		return $ret;
	}

	private function getCountByFilterSubSet($filters, $includeAppliedFilters)
	{
		$selectFilter = $this->productFilter->getSelectFilter(!$includeAppliedFilters);
		$selectFilter->removeFieldList();
		$selectFilter->limit(0);

		foreach ($filters as $filter)
		{
			$filter->defineJoin($selectFilter);

			$cond = $filter->getCondition();
			if (!is_object($cond))
			{
				continue;
			}

			$expression = 'SUM(' . $cond->getExpressionHandle()->toString() . ')';
			$selectFilter->addField($expression, null, 'cnt_' . $filter->getID());
		}

		$query = ActiveRecordModel::createSelectQuery('Product');
		$query->joinTable('Category', 'Product', 'ID', 'categoryID');
		$query->removeFieldList();
		$query->getFilter()->merge($selectFilter);


		$res = ActiveRecordModel::fetchDataFromDB($query);
		$res = $res[0];

		$ret = array();
		foreach ($res as $key => $value)
		{
			$ret[substr($key, 4)] = $value;
		}

		return $ret;
	}
}

?>