<?php

ClassLoader::import('application.model.system.Language');

/**
 *  Abstract database conversion handler
 *
 *  Imports other shopping cart databases into LiveCart
 *
 *  The following data is imported whenever available:
 *      
 *  * products 
 *  * categories
 *  * languages
 *  * currencies
 *  * users
 *  * user addresses
 *  * orders
 *  * delivery zones
 *  * configuration
 *  
 */
abstract class LiveCartImportDriver
{    
    protected $db;
    
    protected $languages = array();
    
    public function __construct($dsn)
    {
        $this->db = Creole::getConnection($this->dsn);
    }
        
    public abstract function getTableMap();    
        
    public function isProduct()
    {
        return false;
    }
    
    public function isCategory()
    {
        return false;
    }
    
    public function isLanguage()
    {
        return false;        
    }
    
    public function isCurrency()
    {
        return false;        
    }
    
    public function isOrder()
    {
        return false;        
    }
    
    public function isAttribute()
    {
        return false;        
    }

    public function getNextLanguage($id = null)
    {
        return null;
    }

    public function getTotalRecordCount($type)
    {
        $tableMap = $this->getTableMap();
        if (isset($tableMap[$type]))
        {
            return array_shift(array_shift($this->db->getDataBySQL('SELECT COUNT(*) FROM `' . $table . '`')));
        }
    }

    protected function getDataBySQL($sql)
    {
		$resultSet = $this->db->executeQuery($sql);
        while ($resultSet->next())
		{
			$dataArray[] = $resultSet->getRow();
		}
		return $dataArray;        
    }
    
    protected function addLanguage(Language $lang)
    {
        $this->languages[] = $lang;
    }
}

?>