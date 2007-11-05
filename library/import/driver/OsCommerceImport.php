<?php

require_once dirname(__file__) . '/../LiveCartImportDriver.php';

class OsCommerceImport extends LiveCartImportDriver
{
    private $languageMap = null;
    
    public function isLanguages()
    {
        return true;
    }
    
    public function getTableMap()
    {
        return array(
                'category' => 'categories',
                'language' => 'languages',
                'manufacturer' => 'manufacturers',
                'order' => 'orders',
                'product' => 'products_attributes',
                'user' => 'customers',
            )
    }
    
    public function getNextLanguage($id = null)
    {
        if (is_null($this->languageMap))
        {
            $this->languageMap = $this->getDataBySQL("SELECT * FROM languages ORDER BY sort_order ASC");
        }
        
        if (empty($this->languageMap))
        {
            return null;
        }
        
        $data = array_shift($this->languageMap);
        $lang = ActiveRecordModel::getNewInstance('Language');
        $lang->setID($data['code']);

        return $lang;
    }
}

?>