<?php

/**
 *	Calculates the number of products found in categories by filters, manufacturers, price intervals, search queries, etc.
 */
class ProductCount
{
	protected $productFilter;
	
	public function __construct(ProductFilter $productFilter)
	{
		$this->productFilter = $productFilter;
	}
	
	public function getCountByFilters()
	{		
		$selectFilter = $this->productFilter->getSelectFilter();		
		$selectFilter->removeFieldList();
		$selectFilter->setLimit(0);
	
		$filters = $this->productFilter->getCategory()->getFilterSet();				

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
	
	public function getCategoryProductCount()
	{
		if (!$this->productFilter->getFilterCount())	
		{
			return $this->productFilter->getCategory()->activeProductCount->get();
		}
		else
		{
			return $this->productFilter->getCategory()->getProductCount($this->productFilter);
		}
	}
	
	public function getCountByPrices()
	{
	  
	}
	
	public function getCountByManufacturers()
	{
	  
	}

	public function getCountBySubCategories()
	{
	  
	}
}

?>