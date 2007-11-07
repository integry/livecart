<?php

require_once dirname(__file__) . '/../LiveCartImportDriver.php';

class OsCommerceImport extends LiveCartImportDriver
{
    private $languageMap = null;
    private $currencyMap = null;
    private $configMap = null;
            
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
    
    public function isLanguage()
    {
        return true;
    }
    
    public function isCurrency()
    {
        return true;
    }

    public function getTableMap()
    {
        return array(
                'Category' => 'categories',
                'Currency' => 'currencies',
                'CustomerOrder' => 'orders',
                'Language' => 'languages',
                'Manufacturer' => 'manufacturers',
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
        $lang->isEnabled->set(true);
        
        return $lang;
    }

    public function getNextCurrency($id = null)
    {
        if (is_null($this->currencyMap))
        {
            $this->currencyMap = $this->getDataBySQL('SELECT * FROM currencies');
        }
        
        if (empty($this->currencyMap))
        {
            return null;
        }
        
        $data = array_shift($this->currencyMap);
        $curr = ActiveRecordModel::getNewInstance('Currency');
        $curr->setID($data['code']);
        $curr->pricePrefix->set($data['symbol_left']);
        $curr->priceSuffix->set($data['symbol_right']);
        $curr->rate->set($data['value']);
        $curr->lastUpdated->set($data['last_updated']);
        
        $curr->isEnabled->set(true);
        if ($this->getConfigValue('DEFAULT_CURRENCY') == $curr->getID())
        {
            $curr->isDefault->set(true);
        }
        
        return $curr;
    }
    
    protected function getConfigValue($key)
    {
        if (empty($this->configMap))
        {
            $config = $this->getDataBySQL('SELECT * FROM configuration');

            foreach ($config as $row)
            {
                $this->configMap[$row['configuration_key']] = $row['configuration_value'];
            }            
        }
        
        if (isset($this->configMap[$key]))
        {
            return $this->configMap[$key];
        }
    }
}

?>