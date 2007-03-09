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
		$filters = $this->productFilter->getCategory()->getFilterSet();				

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
			$cnt = $this->getCountByFilterSet($set); 
			$ret = array_merge($ret, $cnt);
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
        // get price filters
        $c = Config::getInstance();
        
        $k = 0;        
        $filters = array();
        while ($c->isValueSet('PRICE_FILTER_NAME_' . ++$k))
        {
            $filters[$k] = array($c->getValue('PRICE_FILTER_FROM_' . $k), $c->getValue('PRICE_FILTER_TO_' . $k));
        }          
        
		// get product counts
        $selectFilter = $this->productFilter->getSelectFilter();		
		$selectFilter->removeFieldList();
		$selectFilter->setLimit(0);
    
        $query = new ARSelectQueryBuilder();
        $query->includeTable('Product');
        $query->joinTable('ProductPrice', 'Product', 'productID AND (ProductPrice.currencyID = "' . Store::getInstance()->getDefaultCurrencyCode() . '")', 'ID');

        foreach ($filters as $key => $filter)
        {
            $query->addField('SUM(ProductPrice.price >= ' . $filter[0] . ' AND ProductPrice.price <= ' . $filter[1] . ')', null, $key);  
        }
                
        $query->setFilter($selectFilter);       
 
        $data = ActiveRecordModel::getDataBySQL($query->createString());
        $data = array_diff($data[0], array(0));
        
        return $data;
	}
	
	public function getCountByManufacturers()
	{
		$selectFilter = $this->productFilter->getSelectFilter();		
		$selectFilter->removeFieldList();
		$selectFilter->setLimit(0);
		$selectFilter->setOrder(new ARExpressionHandle('cnt'), 'DESC');
        $selectFilter->setGrouping(new ARFieldHandle('Product', 'manufacturerID'));
        $selectFilter->mergeHavingCondition(new MoreThanCond(new ARExpressionHandle('cnt'), 0));        
        $selectFilter->mergeHavingCondition(new NotEqualsCond(new ARFieldHandle('Manufacturer', 'name'), ''));        
                
        $query = new ARSelectQueryBuilder();
        $query->includeTable('Product');
        $query->joinTable('Manufacturer', 'Product', 'ID', 'manufacturerID');
        $query->addField('COUNT(manufacturerID)', null, 'cnt');
        $query->addField('ID', 'Manufacturer');
        $query->addField('name', 'Manufacturer');
        $query->setFilter($selectFilter);       
        
        $data = ActiveRecordModel::getDataBySQL($query->createString());
        
        return $data;
	}

	public function getCountBySubCategories()
	{
	  
	}
	
	private function getCountByFilterSet($filters)
	{
		$selectFilter = $this->productFilter->getSelectFilter();		
		$selectFilter->removeFieldList();
		$selectFilter->setLimit(0);

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
}

?>