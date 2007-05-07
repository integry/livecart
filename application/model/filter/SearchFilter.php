<?php

ClassLoader::import('application.model.filter.FilterInterface');

class SearchFilter implements FilterInterface
{
    private $query;
    
    function __construct($searchQuery)
    {
        $this->query = rawurldecode(preg_replace('/(_)([0-9A-Z]{2})/', '%$2', $searchQuery));
    }
    
    public function getCondition()
    {
		$searchFields = array('name', 'keywords', 'shortDescription', 'longDescription');
		$searchCond = null;
		foreach ($searchFields as $field)
		{
			$cond = new LikeCond(new ARFieldHandle('Product', $field), '%' . $this->query . '%');
			if (!$searchCond)
			{
				$searchCond = $cond;
			}
			else
			{
				$searchCond->addOr($cond);
			}
		}
		
        return $searchCond;
    }
    
    public function defineJoin(ARSelectFilter $filter)
    {
        /* do nothing */
    }
    
    public function getID()
    {
        return 's';
    }
    
    public function toArray()
    {
		$array = array();
		$array['name_lang'] = '"' . $this->query . '"';
		$array['handle'] = preg_replace('/(%)([0-9A-Z]{2})/', '_$2', rawurlencode($this->query));
		$array['ID'] = $this->getID();
		return $array;        
    }
}

?>