<?php

require_once dirname(__file__) . '/../LiveCartImportDriver.php';

class OsCommerceImport extends LiveCartImportDriver
{
    private $languages = array();

    private $configMap = null;

    private $categoryMap = null;

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

	public function isManufacturer()
	{
		return true;
	}

	public function isUser()
	{
		return true;
	}

	public function isCategory()
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

    public function getNextLanguage()
    {
		if (!$data = $this->loadRecord('SELECT * FROM languages ORDER BY sort_order ASC'))
        {
            return null;
        }

		$this->languages[$data['languages_id']] = $data['code'];

        $lang = ActiveRecordModel::getNewInstance('Language');
        $lang->setID($data['code']);
        $lang->isEnabled->set(true);

        return $lang;
    }

    public function getNextCurrency()
    {
		if (!$data = $this->loadRecord('SELECT * FROM currencies'))
        {
            return null;
        }

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

	public function getNextManufacturer()
	{
		if (!$data = $this->loadRecord('SELECT * FROM manufacturers'))
		{
			return null;
		}

		$man = ActiveRecordModel::getNewInstance('Manufacturer');
		$man->setID($data['manufacturers_id']);
		$man->name->set($data['manufacturers_name']);

		return $man;
	}

	public function getNextUser()
	{
		if (!$data = $this->loadRecord('SELECT * FROM customers'))
		{
			return null;
		}

		$rec = User::getNewInstance($data['customers_email_address']);
		$rec->setID($data['customers_id']);
		$rec->password->set($data['customers_password']);
		$rec->firstName->set($data['customers_firstname']);
		$rec->lastName->set($data['customers_lastname']);
		$rec->isEnabled->set(true);

		return $rec;
	}

	public function getNextCategory()
	{
		if (is_null($this->categoryMap))
		{
			$join = $langs = array();
            foreach ($this->languages as $id => $code)
			{
				$join[] = 'LEFT JOIN categories_description AS category_' . $code . ' ON category_' . $code . '.categories_id=categories.categories_id AND category_' . $code . '.language_id=' . $id;
				$langs[] = 'category_' . $code . '.categories_name AS name_' . $code;
			}

			// get all categories
			foreach ($this->getDataBySQL('SELECT *,' . implode(', ', $langs) . ' FROM categories ' . implode(' ', $join) . ' ORDER BY sort_order ASC') as $category)
			{
				$this->categoryMap[$category['categories_id']] = $category;
			}
			
			// get level for each category
			foreach ($this->categoryMap as $id => &$category)
			{
                $level = 0;
                while ($id != 0 && ($level < 100))
                {
                    if (isset($this->categoryMap[$id]['parent_id']))
                    {
                        $id = $this->categoryMap[$id]['parent_id'];
                        $level++;
                    }
                    
                    // parent category does not exist, so remove the category
                    else if ($this->categoryMap[$id]['parent_id'] != 0)
                    {
                        unset($this->categoryMap[$id]);
                        $level = 101;
                    }
                }
                
                // circular reference
                if ($level >= 100)
                {
                    unset($this->categoryMap[$category['categories_id']]);
                }
                else
                {
                    $category['level'] = $level;
                }
            }
            
            usort($this->categoryMap, array($this, 'sortCategories'));
		}

		// root level categories first
		if ($data = array_shift($this->categoryMap))
		{
			$parentNode = 0 == $data['parent_id'] ? Category::getRootNode(Category::LOAD_DATA) : Category::getInstanceById($this->getRealId('Category', $data['parent_id']), Category::LOAD_DATA);
            $rec = Category::getNewInstance($parentNode);
		}
		else
		{
            return null;
        }

        $rec->setID($data['categories_id']);

        foreach ($this->languages as $code)
        {
            $rec->setValueByLang('name', $code, $data['name_' . $code]);
        }

		return $rec;
	}

    private function sortCategories($a, $b)
    {
        if ($a['level'] == $b['level'])
        {
            if ($a['sort_order'] == $b['sort_order'])
            {
                return 0;
            }
            else
            {
                return $a['sort_order'] > $b['sort_order'] ? 1 : -1;
            }            
        }
        
        return $a['level'] > $b['level'] ? 1 : -1;
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