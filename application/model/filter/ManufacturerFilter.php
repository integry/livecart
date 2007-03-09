<?php

ClassLoader::import('application.model.filter.FilterInterface');

class ManufacturerFilter implements FilterInterface
{
    private $manufacturerID = 0;
    
    private $manufacturerName = '';
        
    function __construct($manufacturerID, $manufacturerName)
    {
        $this->manufacturerID = $manufacturerID;
        $this->manufacturerName = $manufacturerName;
    }
    
    public function getCondition()
    {
        return new EqualsCond(new ARFieldHandle('Product', 'manufacturerID'), $this->manufacturerID);    
    }
    
    public function defineJoin(ARSelectFilter $filter)
    {
        /* do nothing */
    }
    
    public function getID()
    {
        return 'm' . $this->manufacturerID;
    }
    
    public function toArray()
    {
		$array = array();
		$array['name_lang'] = $this->manufacturerName;
		$array['handle'] = Store::createHandleString($array['name_lang']);
		$array['ID'] = $this->getID();
		return $array;        
    }
}

?>