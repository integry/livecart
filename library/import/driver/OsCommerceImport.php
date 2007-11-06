<?php

require_once dirname(__file__) . '/../LiveCartImportDriver.php';

class OsCommerceImport extends LiveCartImportDriver
{
    private $languageMap = null;
    
    public function getName()
    {
        return 'osCommerce';
    }
    
    public function isPathValid()
    {
        // no path provided - won't be able to import images
        if (!$this->path)
        {
            return true;
        }
        
        foreach (array('images', 'address_book.php', 'checkout_process.php') as $file)
        {
            if (!file_exists($this->path . '/' . $file))
            {
                return false;
            }
        }
        
        return true;
    }
    
    public function isLanguages()
    {
        return true;
    }
    
    public function getTableMap()
    {
        return array(
                'Category' => 'categories',
                'Language' => 'languages',
                'Manufacturer' => 'manufacturers',
                'CustomerOrder' => 'orders',
                'Product' => 'products_attributes',
                'User' => 'customers',
            );
    }
    
    public function getNextLanguage($id = null)
    {
        if (is_null($this->languageMap))
        {
            $this->languageMap = $this->getDataBySQL('SELECT * FROM languages ORDER BY sort_order ASC');
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