<?php

require_once dirname(__file__) . '/../LiveCartImportDriver.php';

class OsCommerceImport extends LiveCartImportDriver
{
	protected $languages = array();

	protected $configMap = null;

	protected $categoryMap = null;

	protected $productSql;

	private $languagesTruncated;
	private $currenciesTruncated;

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

	public function isProduct()
	{
		return true;
	}

	public function isCustomerOrder()
	{
		return true;
	}

	public function isBillingAddress()
	{
		return true;
	}

	public function getVerificationTableNames()
	{
		return array('categories', 'currencies', 'orders', 'languages', 'manufacturers', 'products', 'customers');
	}

	public function getTableMap()
	{
		return array(
				'Category' => $this->getTablePrefix() . 'categories',
				'Currency' => $this->getTablePrefix() . 'currencies',
				'CustomerOrder' => $this->getTablePrefix() . 'orders',
				'Language' => $this->getTablePrefix() . 'languages',
				'Manufacturer' => $this->getTablePrefix() . 'manufacturers',
				'Product' => $this->getTablePrefix() . 'products',
				'User' => $this->getTablePrefix() . 'customers',
				'BillingAddress' => array('SELECT COUNT(*) FROM ' . $this->getTablePrefix() . 'address_book LEFT JOIN ' . $this->getTablePrefix() . 'customers ON ' . $this->getTablePrefix() . 'address_book.customers_id=' . $this->getTablePrefix() . 'customers.customers_id WHERE ' . $this->getTablePrefix() . 'customers.customers_id IS NOT NULL' => $this->getTablePrefix() . 'address_book')
			);
	}

	public function getNextLanguage()
	{
		if (!$this->languagesTruncated)
		{
			ActiveRecord::executeUpdate('DELETE FROM Language');
			$this->languagesTruncated = true;
		}

		if (!$data = $this->loadRecord('SELECT * FROM ' . $this->getTablePrefix() . 'languages ORDER BY sort_order ASC'))
		{
			return null;
		}

		$this->languages[$data['languages_id']] = $data['code'];

		$lang = ActiveRecordModel::getInstanceByIdIfExists('Language', $data['code']);
		$lang->isEnabled->set(true);

		if (1 == $data['sort_order'])
		{
			$lang->isDefault->set(true);
		}

		return $lang;
	}

	public function getNextCurrency()
	{
		if (!$this->currenciesTruncated)
		{
			ActiveRecord::executeUpdate('DELETE FROM Currency');
			$this->currenciesTruncated = true;
		}

		if (!$data = $this->loadRecord('SELECT * FROM ' . $this->getTablePrefix() . 'currencies'))
		{
			return null;
		}

		$curr = ActiveRecordModel::getInstanceByIdIfExists('Currency', $data['code']);
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
		if (!$data = $this->loadRecord('SELECT * FROM ' . $this->getTablePrefix() . 'manufacturers'))
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
		if (!$data = $this->loadRecord('SELECT * FROM ' . $this->getTablePrefix() . 'customers'))
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
				list($join[], $langs[]) = $this->joinCategoryFields($id, $code);
			}

			// get all categories
			$q = 'SELECT *,' . $this->getTablePrefix() . 'categories.categories_id AS categories_id,' . implode(', ', $langs) . ' FROM ' . $this->getTablePrefix() . 'categories ' . implode(' ', $join) . ' ORDER BY sort_order ASC';
			foreach ($this->getDataBySQL($q) as $category)
			{
				$this->categoryMap[$category['categories_id']] = $category;
			}

			// get level for each category
			foreach ($this->categoryMap as $id => &$category)
			{
				$level = 0;
				while ($id != 0 && ($level < 100))
				{
					$level++;

					if (isset($this->categoryMap[$id]['parent_id']))
					{
						$id = $this->categoryMap[$id]['parent_id'];
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
			$parentNode = 0 == $data['parent_id'] ? Category::getRootNode() : Category::getInstanceById($this->getRealId('Category', $data['parent_id']));
			$rec = Category::getNewInstance($parentNode);
		}
		else
		{
			return null;
		}

		$rec->setID($data['categories_id']);
		$rec->isEnabled->set(true);

		foreach ($this->languages as $code)
		{
			$rec->setValueByLang('name', $code, $data['name_' . $code]);
		}

		//product image
		if ($data['categories_image'])
		{
			$this->importCategoryImage($rec, $this->path . '/images/' . $data['categories_image']);
		}

		$rec->rawData = $data;

		return $rec;
	}

	protected function joinCategoryFields($id, $code)
	{
		return array('LEFT JOIN ' . $this->getTablePrefix() . 'categories_description AS category_' . $code . ' ON ' . $this->getTablePrefix() . 'category_' . $code . '.categories_id=' . $this->getTablePrefix() . 'categories.categories_id AND ' . $this->getTablePrefix() . 'category_' . $code . '.language_id=' . $id,
					 'category_' . $code . '.categories_name AS name_' . $code
					);
	}

	public function getNextProduct()
	{
		if (!$this->productSql)
		{
			foreach ($this->languages as $id => $code)
			{
				list($join[], $langs[]) = $this->joinProductFields($id, $code);
			}

			$this->productSql = 'SELECT *,' . implode(', ', $langs) . ' FROM ' . $this->getTablePrefix() . 'products ' . implode(' ', $join) . ' LEFT JOIN ' . $this->getTablePrefix() . 'products_to_categories ON ' . $this->getTablePrefix() . 'products.products_id=' . $this->getTablePrefix() . 'products_to_categories.products_id LEFT JOIN ' . $this->getTablePrefix() . 'categories ON ' . $this->getTablePrefix() . 'products_to_categories.categories_id=' . $this->getTablePrefix() . 'categories.categories_id  WHERE ' . $this->getTablePrefix() . 'categories.categories_id IS NOT NULL';
		}

		if (!$data = $this->loadRecord($this->productSql))
		{
			return null;
		}

		$rec = Product::getNewInstance(Category::getInstanceById($this->getRealId('Category', $data['categories_id'])));
		$rec->setID($data['products_id']);

		foreach ($this->languages as $code)
		{
			$rec->setValueByLang('name', $code, $data['name_' . $code]);
			$rec->setValueByLang('longDescription', $code, $data['descr_' . $code]);

			// use the first line or paragraph of the long description as the short description
			$short = array_shift(preg_split("/\n|\<br/", $data['descr_' . $code]));
			$rec->setValueByLang('shortDescription', $code, $short);
		}

		if ($data['manufacturers_id'])
		{
			$rec->manufacturer->set(Manufacturer::getInstanceById($this->getRealId('Manufacturer', $data['manufacturers_id'])));
		}

		$rec->sku->set($data['products_model']);
		$rec->URL->set($data['products_url']);
		$rec->isEnabled->set((int)(1 == $data['products_status']));
		$rec->shippingWeight->set($data['products_weight']);
		$rec->stockCount->set($data['products_quantity']);
		$rec->dateCreated->set($data['products_date_added']);

		$rec->setPrice($this->getConfigValue('DEFAULT_CURRENCY'), $data['products_price']);

		//product image
		if ($data['products_image'])
		{
			$this->importProductImage($rec, $this->path . '/images/' . $data['products_image']);
		}

		$rec->rawData = $data;

		return $rec;
	}

	protected function joinProductFields($id, $code)
	{
		return array('LEFT JOIN ' . $this->getTablePrefix() . 'products_description AS product_' . $code . ' ON ' . $this->getTablePrefix() . 'product_' . $code . '.products_id=' . $this->getTablePrefix() . 'products.products_id AND product_' . $code . '.language_id=' . $id,
					 'product_' . $code . '.products_url AS products_url, product_' . $code . '.products_name AS name_' . $code . ', ' . 'product_' . $code . '.products_description AS descr_' . $code
					);
	}

	public function getNextCustomerOrder()
	{
		if (!$data = $this->loadRecord('SELECT *, ' . $this->getTablePrefix() . 'orders.orders_id AS id, ' . $this->getTablePrefix() . 'orders_total.value FROM ' . $this->getTablePrefix() . 'orders LEFT JOIN ' . $this->getTablePrefix() . 'orders_total ON (' . $this->getTablePrefix() . 'orders.orders_id=' . $this->getTablePrefix() . 'orders_total.orders_id AND class="ot_shipping")'))
		{
			return null;
		}

		$order = CustomerOrder::getNewInstance(User::getInstanceById($this->getRealId('User', $data['customers_id'])));
		$order->currency->set(Currency::getInstanceById($data['currency']));
		$order->dateCompleted->set($data['date_purchased']);

		// products
		$tax = 0;
		foreach ($this->getDataBySql('SELECT * FROM ' . $this->getTablePrefix() . 'orders_products WHERE ' . $this->getTablePrefix() . 'orders_id=' . $data['id']) as $prod)
		{
			$product = Product::getInstanceById($this->getRealId('Product', $prod['products_id']));
			$order->addProduct($product, $prod['products_quantity'], true);

			$item = array_shift($order->getItemsByProduct($product));
			$item->price->set($prod['products_price']);
			$tax += $prod['products_tax'];
		}

		// addresses
		$order->shippingAddress->set($this->getUserAddress($data, 'delivery_'));
		$order->billingAddress->set($this->getUserAddress($data, 'billing_'));

		// assume that all orders are paid and shipped
		$order->status->set(CustomerOrder::STATUS_SHIPPED);
		$order->isPaid->set(true);

		$data['taxAmount'] = $tax;
		$order->rawData = $data;

		return $order;
	}

	public function getNextBillingAddress()
	{
		if (!$data = $this->loadRecord('SELECT * FROM ' . $this->getTablePrefix() . 'address_book LEFT JOIN ' . $this->getTablePrefix() . 'countries ON ' . $this->getTablePrefix() . 'address_book.entry_country_id=' . $this->getTablePrefix() . 'countries.countries_id LEFT JOIN ' . $this->getTablePrefix() . 'customers ON ' . $this->getTablePrefix() . 'address_book.customers_id=' . $this->getTablePrefix() . 'customers.customers_id WHERE ' . $this->getTablePrefix() . 'customers.customers_id IS NOT NULL'))
		{
			return null;
		}

		$address = $this->getUserAddress($data, 'entry_');
		$address->countryID->set($data['countries_iso_code_2']);

		return BillingAddress::getNewInstance(User::getInstanceById($this->getRealId('User', $data['customers_id'])), $address);
	}

	private function getUserAddress($data, $prefix)
	{
		$address = UserAddress::getNewInstance();
		$map = array(
				'company' => 'companyName',
				'street_address' => 'address1',
				'city' => 'city',
				'postcode' => 'postalCode',
				'state' => 'stateName',
				'firstname' => 'firstName',
				'lastname' => 'lastName',
			   );

		foreach ($map as $osc => $lc)
		{
			if (isset($data[$prefix . $osc]))
			{
				$address->$lc->set($data[$prefix . $osc]);
			}
		}

		if (!empty($data[$prefix . 'name']))
		{
			$names = explode(' ', $data[$prefix . 'name'], 2);
			$address->firstName->set(array_shift($names));
			$address->lastName->set(array_shift($names));
		}

		if (isset($data['customers_telephone']))
		{
			$address->phone->set($data['customers_telephone']);
		}

		if (isset($data[$prefix . 'country']))
		{
			$country = array_search($data[$prefix . 'country'], Locale::getInstance('en')->info()->getAllCountries());
			if (!$country)
			{
				$country = 'US';
			}

			$address->countryID->set($country);
		}

		return $address;
	}

	public function saveBillingAddress(BillingAddress $address)
	{
		$address->userAddress->get()->save();
		return $address->save();
	}

	public function saveCustomerOrder(CustomerOrder $order)
	{
		$order->shippingAddress->get()->save();
		$order->billingAddress->get()->save();

		$order->save();

		$shipment = $order->getShipments()->get(0);
		$shipment->shippingAmount->set($order->rawData['value']);
		$shipment->save();

		if ($order->rawData['taxAmount'] > 0)
		{
			$tax = ActiveRecordModel::getNewInstance('ShipmentTax');
			$tax->shipment->set($shipment);
			$tax->amount->set($order->rawData['taxAmount']);
			$tax->save();

			$shipment->addFixedTax($tax);
			$shipment->status->set(Shipment::STATUS_SHIPPED);
			$shipment->save();
		}

		return parent::saveCustomerOrder($order);
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
			$config = $this->getDataBySQL('SELECT * FROM ' . $this->getTablePrefix() . 'configuration');

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
