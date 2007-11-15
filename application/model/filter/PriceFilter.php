<?php

ClassLoader::import('application.model.filter.FilterInterface');
ClassLoader::import('application.helper.CreateHandleString');

/**
 * Filter product list by price. Price intervals are pre-defined (for now).
 *
 * @package application.model.filter
 * @author Integry Systems <http://integry.com>
 */
class PriceFilter implements FilterInterface
{
	private $filterID = 0;
	
	private $name = '';
	private $priceFrom = 0;
	private $priceTo = 0;
			
	function __construct($filterID, LiveCart $application)
	{
		$this->filterID = $filterID;
		$this->application = $application;
		$c = $this->application->getConfig();
		
		$this->name = $c->get('PRICE_FILTER_NAME_' . $filterID);
		$this->priceFrom = $c->get('PRICE_FILTER_FROM_' . $filterID);
		$this->priceTo = $c->get('PRICE_FILTER_TO_' . $filterID);
	}
	
	public function getCondition()
	{
		$from = new EqualsOrMoreCond(new ARFieldHandle('ProductPrice', 'price'), $this->priceFrom);	
		$to =   new EqualsOrLessCond(new ARFieldHandle('ProductPrice', 'price'), $this->priceTo);	
		$from->addAND($to);
		
		return $from;
	}
	
	public function defineJoin(ARSelectFilter $filter)
	{
		$filter->joinTable('ProductPrice', 'Product', 'productID AND (ProductPrice.currencyID = "' . $this->application->getDefaultCurrencyCode() . '")', 'ID');
	}
	
	public function getID()
	{
		return 'p' . $this->filterID;
	}
	
	public function toArray()
	{
		$array = array();
		$array['name_lang'] = $this->name;
		$array['handle'] = createHandleString($this->name);
		$array['ID'] = $this->getID();
		return $array;		
	}
}

?>