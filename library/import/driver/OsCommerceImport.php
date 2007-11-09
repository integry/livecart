<?php

require_once dirname(__file__) . '/../LiveCartImportDriver.php';

class OsCommerceImport extends LiveCartImportDriver
{
    private $languages = array();

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

	public function isManufacturer()
	{
		return true;
	}

	public function isUser()
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
		if (!$this->categoryMap)
		{
			foreach ($this->languages as $id => $code)
			{
				$join[] = 'LEFT JOIN categories_description AS category_' . $code . ' ON categories_description.categories_id=categories.categories_id AND categories_description.language_id=' . $id;
			}

			// get all categories
			foreach ($this->db->getDataBySQL('SELECT * FROM categories ' . implode(' ', $join) . ' ORDER BY sort_order ASC') as $category)
			{
				$this->categoryMap[$category['categories_id']] = $category;
			}
		}

		$this->categoryMap = $this->db->getDataBySQL('SELECT * FROM categories ORDER BY sort_order ASC');

		// root level categories first
		if ($data = $this->loadRecord('SELECT * FROM categories WHERE parent_id = 0'))
		{
			$rec = Category::getNewInstance(Category::getRootNode());
		}
		else if ($data = $this->loadRecord('SELECT * FROM categories WHERE parent_id > 0'))
		{
			$parent = $this->getRealId('Category', $data['parent_id']);
			$rec = Category::getNewInstance(Category::getInstanceById($parent));
		}

		$rec = User::getNewInstance($data['customers_email_address']);
		$rec->setID($data['customers_id']);
		$rec->password->set($data['customers_password']);
		$rec->firstName->set($data['customers_firstname']);
		$rec->lastName->set($data['customers_lastname']);
		$rec->isEnabled->set(true);

		return $rec;
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