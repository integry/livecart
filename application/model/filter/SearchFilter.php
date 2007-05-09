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
		// analyze search query
		// find exact phrases first
		$query = $this->query;
		preg_match_all('/"(.*)"/sU', $query, $matches);
		
		$phrases = array();
		if ($matches[1])
		{
			$phrases = $matches[1];
		}
		
		$query = preg_replace('/"(.*)"/sU', '', $query);
		$query = preg_replace('/[-,\._!\?\\/]/', ' ', $query);
		$query = preg_replace('/ {2,}/', ' ', $query);
		
		$query = trim($query);
		
		$phrases = array_merge($phrases, explode(' ', $query));
		
		$searchFields = array('name', 'keywords', 'shortDescription', 'longDescription');
		
		$conditions = array();
		
		foreach ($phrases as $phrase)
		{
			$searchCond = null;
			foreach ($searchFields as $field)
			{
				$cond = new LikeCond(new ARFieldHandle('Product', $field), '%' . $phrase . '%');
				if (!$searchCond)
				{
					$searchCond = $cond;
				}
				else
				{
					$searchCond->addOr($cond);
				}
			}
			
			$conditions[] = $searchCond;			
		}
			
        return new AndChainCondition($conditions);
    }
    
    public function defineJoin(ARSelectFilter $filter)
    {
        /* do nothing */
    }
    
    public function getID()
    {
        return 's';
    }
    
    public function getKeywords()
    {
        return $this->query;
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