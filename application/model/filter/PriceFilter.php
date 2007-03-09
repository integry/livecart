<?php

ClassLoader::import('application.model.filter.FilterInterface');

class PriceFilter implements FilterInterface
{
    private $filterID = 0;
    
    private $name = '';
    private $priceFrom = 0;
    private $priceTo = 0;
            
    function __construct($filterID)
    {
        $this->filterID = $filterID;
        
        $c = Config::getInstance();
           
        $this->name = $c->getValue('PRICE_FILTER_NAME_' . $filterID);
        $this->priceFrom = $c->getValue('PRICE_FILTER_FROM_' . $filterID);
        $this->priceTo = $c->getValue('PRICE_FILTER_TO_' . $filterID);
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
        $filter->joinTable('ProductPrice', 'Product', 'productID AND (ProductPrice.currencyID = "' . Store::getInstance()->getDefaultCurrencyCode() . '")', 'ID');
    }
    
    public function getID()
    {
        return 'p' . $this->filterID;
    }
    
    public function toArray()
    {
		$array = array();
		$array['name_lang'] = $this->name;
		$array['handle'] = Store::createHandleString($this->name);
		$array['ID'] = $this->getID();
		return $array;        
    }
}

?>