<?php

/**
 *
 * @package application.model.product
 *
 */
class ProductFilter
{
	private $selectFilter = null;
	private $condition = null;	

	public function __construct()
	{
		$this->selectFilter = new ARSelectFilter();
	}
	
	/**
	 * Applies a filter to a product set
	 *
	 * @param Filter $filter
	 * @todo implement
	 */
	public function applyFilter(Filter $filter)
	{
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