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
		$this->selectFilter = $filter;
	}
	
	/**
	 * Applies a filter to a product set
	 *
	 * @param Filter $filter
	 * @todo implement
	 */
	public function applyFilter(Filter $filter)
	{
		$this->addCondition($filter->createCondition());
	}

	public function searchNameByLang($language, $needle)
	{
	  	// first we'll filter out all the records containing the needle (for any language)
		$cond = new LikeCond(new ARFieldHandle('Product', 'name'), $needle);
	  	
	  	// and then we'll narrow down the result site by leaving only records 
		// that contain the needle in the required language
		$regexp = new RegexpCond(new ARFieldHandle('Product', 'name'), '.*"' . strtolower($language) . '";s:[0-9]+:".*' . $needle . '.*"');
		
		$cond->addAND($regexp);
		
		$this->addCondition($cond);
	}
	
	public function getSelectFilter()
	{
	  	$this->selectFilter->setCondition($this->condition);
	  	return $this->selectFilter;
	}
	
	public function getCountByFilters($filters)
	{
		$this->selectFilter->removeFieldList();
		foreach ($filters as $filter)
		{
			echo $filter->getCondition()->createChain() . '<br>';
			$expression = 'SUM(' . $filter->getCondition()->getExpressionHandle()->toString() . ')';
			$this->selectFilter->addField($expression, null, '_filterCount_' . $filter->getID());	
		}
		
		$query = ActiveRecordModel::createSelectQuery('Product');
		$query->removeFieldList();
		$query->getFilter()->merge($this->selectFilter);
		
		$data = ActiveRecordModel::fetchDataFromDB($query);
		
		return $data;
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