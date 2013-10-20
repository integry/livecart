<?php

ClassLoader::importNow('application/helper/CreateHandleString');

/**
 * Filter product list by price. Price intervals are pre-defined (for now).
 *
 * @package application/model/filter
 * @author Integry Systems <http://integry.com>
 */
class PriceFilter implements FilterInterface
{
	const MAX_FILTER_COUNT = 10;
	private $filterID = 0;
	private $name = '';
	private $priceFrom = 0;
	private $priceTo = 0;
	private $lastInSetName = '';

	function __construct($filterID, LiveCart $application)
	{
		$this->filterID = $filterID;
		$this->application = $application;
		$c = $this->application->getConfig();

		for($definedFilterCount=1; $definedFilterCount<=self::MAX_FILTER_COUNT; $definedFilterCount++)
		{
			if(!$c->get('PRICE_FILTER_FROM_' . $definedFilterCount) && !$c->get('PRICE_FILTER_TO_' . $definedFilterCount)) //has() returns true if values are ''
			{
				break;
			}
		}
		$definedFilterCount--;

		$this->name = $c->get('PRICE_FILTER_NAME_' . $filterID);
		$setCustomName = !strlen(trim($this->name));

		$this->priceFrom = $c->get('PRICE_FILTER_FROM_' . $filterID);
		$this->priceTo = $c->get('PRICE_FILTER_TO_' . $filterID);

		$requestCurrencyID = $this->application->controllerInstance->getRequestCurrency();
		$defaultCurrency = $this->application->getDefaultCurrency();
		if($defaultCurrency->getID() != $requestCurrencyID)
		{
			$setCustomName = true;
			$requestCurrency = Currency::getInstanceById($requestCurrencyID);
			foreach(array('priceFrom', 'priceTo') as $price)
			{
				$this->$price = $requestCurrency->convertAmount($defaultCurrency, $this->$price);
				if($this->$price != 0)
				{
					$this->$price = $requestCurrency->round($this->$price);
				}
			}
		}

		if($setCustomName)
		{
			if($this->priceFrom == 0)
			{
				$this->name = $this->application->maketext('_to [_1]', $this->priceTo);
			}
			else if($definedFilterCount == $filterID && $this->priceFrom * 2 < $this->priceTo)
			{
				 $this->name = $this->application->maketext('_more_than [_1]', $this->priceFrom);
			}
			else
			{
				$this->name = $this->application->maketext('[_1] - [_2]', array($this->priceFrom, $this->priceTo));
			}
		}
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
		$array['last_in_set_name_lang'] = $this->lastInSetName;
		$array['handle'] = createHandleString($this->name);
		$array['ID'] = $this->getID();
		return $array;
	}
}

?>