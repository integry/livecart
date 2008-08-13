<?php

ClassLoader::import('application.helper.CreateHandleString');

require_once dirname(__file__) . '/../LiveCartImportDriver.php';

class ShopScriptImport extends LiveCartImportDriver
{
	protected $categoryMap = null;

	protected $productSql;
	private $specFieldSql;

	private $defLang;
	private $attributes = array();
	private $statusMap = array();
	private $countryMap = array();
	private $countryNameMap = array();

	public function getName()
	{
		return 'Shop-Script';
	}

	public function isPathValid()
	{
		// no path provided - won't be able to import images
		if (!$this->path)
		{
			return true;
		}

		if (substr(strtolower($this->path), 0, 7) == 'http://')
		{
			return true;
		}

		foreach (array('admin.php', 'core_functions', 'checklogin.php') as $file)
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
		return false;
	}

	public function isState()
	{
		return false;
	}

	public function isUser()
	{
		return true;
	}

	public function isBillingAddress()
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

	public function isProductRelationship()
	{
		return true;
	}

	public function isNewsletterSubscriber()
	{
		return true;
	}

	public function isStaticPage()
	{
		return true;
	}

	public function isSpecField()
	{
		return true;
	}

	public function getVerificationTableNames()
	{
		return array('categories', 'custgroups', 'customer_addresses', 'product_options', 'products_opt_val_variants', 'shopping_cart_items_content');
	}

	public function getTableMap()
	{
		return array(
				'Category' => $this->getTablePrefix() . 'categories',
				'Currency' => $this->getTablePrefix() . 'currency_types',
				'CustomerOrder' => $this->getTablePrefix() . 'orders',
				'BillingAddress' => $this->getTablePrefix() . 'customer_addresses',
				'Product' => $this->getTablePrefix() . 'products',
				'ProductRelationship' => $this->getTablePrefix() . 'related_items',
				'User' => $this->getTablePrefix() . 'customer_addresses',
				'NewsletterSubscriber' => $this->getTablePrefix() . 'subscribers',
				'SpecField' => $this->getTablePrefix() . 'product_options',
				'StaticPage' => $this->getTablePrefix() . 'aux_pages',
			);
	}

	public function getNextLanguage()
	{
		if ($this->defLang)
		{
			return null;
		}

		// default language is set to English
		$id = 'en';

		if (!$lang = ActiveRecordModel::getInstanceByIdIfExists('Language', $id, false))
		{
			$lang = ActiveRecordModel::getNewInstance('Language');
			$lang->setID($id);
			$lang->isEnabled->set(true);
			$lang->isDefault->set(true);
		}

		$this->defLang = $lang->getID();

		return $lang;
	}

	public function getNextState()
	{
		if (!$data = $this->loadRecord('SELECT * FROM ' . $this->getTablePrefix() . 'states'))
		{
			return null;
		}

		$rec = ActiveRecordModel::getNewInstance('State');

		foreach (array(
					'countryID' => 'country_code',
					'code' => 'code',
					'name' => 'state',
				) as $lc => $xc)
		{
			$rec->$lc->set($data[$xc]);
		}

		return $rec;
	}

	public function getNextCurrency()
	{
		if (!$data = $this->loadRecord('SELECT * FROM ' . $this->getTablePrefix() . 'currency_types ORDER BY sort_order ASC'))
		{
			return null;
		}

		if (!$currency = ActiveRecordModel::getInstanceByIDIfExists('Currency', $data['currency_iso_3'], false))
		{
			$currency = Currency::getNewInstance($data['currency_iso_3']);
			$currency->rate->set($data['currency_value']);
		}

		if ($this->getConfigValue('CONF_DEFAULT_CURRENCY') == $data['CID'])
		{
			$f = new ARUpdateFilter();
			$f->addModifier('Currency.isDefault', 0);
			ActiveRecordModel::updateRecordSet('Currency', $f);
			$currency->isDefault->set(true);
		}

		return $currency;
	}

	public function getNextUser()
	{
		if (!$data = $this->loadRecord('SELECT DISTINCT(Email) AS em, ' . $this->getTablePrefix() . 'customers.* FROM ' . $this->getTablePrefix() . 'customers GROUP BY em'))
		{
			return null;
		}

		if (!$rec = User::getInstanceByEmail($data['Email']))
		{
			$rec = User::getNewInstance($data['Email']);

			foreach (array(
						'firstName' => 'first_name',
						'lastName' => 'last_name',
						'dateCreated' => 'reg_datetime',
					) as $lc => $xc)
			{
				$rec->$lc->set($data[$xc]);
			}

			$rec->setPassword(base64_decode($data['cust_password']));
			$rec->isEnabled->set(1);
			$rec->setID($data['customerID']);
		}

		return $rec;
	}

	public function getNextBillingAddress()
	{
		$addressTable = $this->getTablePrefix() . 'customer_addresses';
		$userTable = $this->getTablePrefix() . 'customers';
		if (!$data = $this->loadRecord('SELECT * FROM ' . $addressTable . ' LEFT JOIN ' . $userTable . ' ON ' . $userTable . '.addressID = ' . $addressTable . '.customerID WHERE ' . $userTable .'.customerID IS NOT NULL'))
		{
			return null;
		}

		$address = UserAddress::getNewInstance();
		$map = array(
				'address' => 'address1',
				'city' => 'city',
				'zip' => 'postalCode',
				'state' => 'stateName',
				'first_name' => 'firstName',
				'last_name' => 'lastName',
			   );

		foreach ($map as $osc => $lc)
		{
			if (isset($data[$osc]))
			{
				$address->$lc->set($data[$osc]);
			}
		}

		$address->countryID->set($this->getCountryByID($data['countryID']));

		$user = ActiveRecordModel::getInstanceByIDIfExists('User', $this->getRealId('User', $data['customerID']), false);
		if (!$user || !$user->isExistingRecord())
		{
			return $this->getNextBillingAddress();
		}

		$billing = BillingAddress::getNewInstance($user, $address);

		$billing->save();

		// default address
		if ($data['Email'])
		{
			$user->defaultBillingAddress->set($billing);
			$user->save();
		}

		return $billing;
	}

	public function getNextCategory()
	{
		if (is_null($this->categoryMap))
		{
			// get all categories
			$sql = 'SELECT * FROM ' . $this->getTablePrefix() . 'categories WHERE categoryID > 1';
			foreach ($this->getDataBySQL($sql) as $category)
			{
				$this->categoryMap[$category['categoryID']] = $category;
			}

			// get level for each category
			foreach ($this->categoryMap as $id => &$category)
			{
				if (1 == $category['parent'])
				{
					$category['parent'] = 0;
				}

				$level = 0;
				while ($id != 0 && ($level < 100))
				{
					$level++;

					if (isset($this->categoryMap[$id]['parent']))
					{
						$id = $this->categoryMap[$id]['parent'];
					}

					// parent category does not exist, so remove the category
					else if (!isset($this->categoryMap[$id]) || ($this->categoryMap[$id]['parent'] != 0))
					{
						unset($this->categoryMap[$id]);
						$level = 101;
					}
				}

				// circular reference
				if ($level >= 100)
				{
					unset($this->categoryMap[$category['categoryID']]);
				}
				else
				{
					$category['level'] = $level;
				}
			}

			$this->categoryIds = array_keys($this->categoryMap);

			usort($this->categoryMap, array($this, 'sortCategories'));
		}

		// root level categories first
		if ($data = array_shift($this->categoryMap))
		{
			$parentNode = 0 == $data['parent'] ? Category::getRootNode() : Category::getInstanceById($this->getRealId('Category', $data['parent']));
			$rec = Category::getNewInstance($parentNode);
		}
		else
		{
			return null;
		}

		$rec->setID($data['categoryID']);
		$rec->isEnabled->set(true);
		$rec->keywords->set($data['meta_keywords']);
		$rec->setValueByLang('name', $this->defLang, $data['name']);
		$rec->setValueByLang('description', $this->defLang, $data['description']);

		//images
		if ($data['picture'])
		{
			//$this->importCategoryImage($rec, $this->path . '/' . $data['picture']);
		}

		$rec->rawData = $data;

		return $rec;
	}

	public function getNextProductRelationship()
	{
		if (!$data = $this->loadRecord('SELECT * FROM ' . $this->getTablePrefix() . 'related_items'))
		{
			return null;
		}

		$owner = Product::getInstanceByID($this->getRealId('Product', $data['Owner']), Product::LOAD_DATA);
		$target = Product::getInstanceByID($this->getRealId('Product', $data['productID']), Product::LOAD_DATA);

		return ProductRelationship::getNewInstance($owner, $target);
	}

	public function getNextNewsletterSubscriber()
	{
		if (!$data = $this->loadRecord('SELECT * FROM ' . $this->getTablePrefix() . 'subscribers LEFT JOIN ' . $this->getTablePrefix() . 'customers ON ' . $this->getTablePrefix() . 'customers.customerID=' . $this->getTablePrefix() . 'subscribers.customerID WHERE ' . $this->getTablePrefix() . 'subscribers.customerID IS NULL OR (' . $this->getTablePrefix() . 'customers.customerID IS NOT NULL)'))
		{
			return null;
		}

		if (empty($data['Email']))
		{
			return $this->getNextNewsletterSubscriber();
		}

		$subscriber = NewsletterSubscriber::getNewInstanceByEmail($data['Email']);
		if ($data['customerID'])
		{
			if ($user = ActiveRecordModel::getInstanceByIDIfExists('User', $this->getRealId('User', $data['customerID'], false)))
			{
				if ($user->isExistingRecord())
				{
					$subscriber->user->set($user);
				}
			}
		}
		else
		{
			if ($user = User::getInstanceByEmail($data['Email']))
			{
				$subscriber->user->set($user);
			}
		}

		$subscriber->isEnabled->set(true);

		return $subscriber;
	}

	public function getNextStaticPage()
	{
		if (!$data = $this->loadRecord('SELECT * FROM ' . $this->getTablePrefix() . 'aux_pages'))
		{
			return null;
		}

		$page = StaticPage::getNewInstance();
		$page->setValueByLang('title', $this->defLang, $data['aux_page_name']);
		$page->setValueByLang('text', $this->defLang, $data['aux_page_text']);
		$page->isInformationBox->set(true);

		return $page;
	}

	public function getNextSpecField()
	{
		if (is_null($this->specFieldSql))
		{
			// get all categories
			$this->specFieldSql = 'SELECT * FROM ' . $this->getTablePrefix() . 'product_options ORDER BY sort_order ASC';
		}

		if (!$data = $this->loadRecord($this->specFieldSql))
		{
			return null;
		}

		$this->attributes[] = $data;

		$rec = SpecField::getNewInstance(Category::getRootNode(), SpecField::DATATYPE_TEXT, SpecField::TYPE_TEXT_SIMPLE);
		$rec->setID($data['optionID']);
		$rec->handle->set(createHandleString($data['name']));
		$rec->setValueByLang('name', $this->defLang, $data['name']);
		$rec->isDisplayed->set(true);

		return $rec;
	}

	public function getNextProduct()
	{
		if (!$this->productSql)
		{
			$fields = array($this->getTablePrefix() . 'products.*');
			$join = array();

			foreach ($this->attributes as $attr)
			{
				$join[] = 'LEFT JOIN ' . $this->getTablePrefix() . 'product_options_values  AS extra_' . $attr['optionID'] . ' ON (extra_' . $attr['optionID'] . '.productID=' . $this->getTablePrefix() . 'products.productID AND extra_' . $attr['optionID'] . '.optionID=' . $attr['optionID'] . ')';
				$fields[] = 'extra_' . $attr['optionID'] . '.option_value AS extrafield_' . $attr['optionID'];
			}

			$validCats = implode(',', $this->categoryIds);

			$this->productSql = 'SELECT ' . implode(',', $fields) . ' FROM ' . $this->getTablePrefix() . 'products ' . implode(' ', $join) . ' LEFT JOIN ' . $this->getTablePrefix() . 'categories AS cat ON cat.categoryID=' . $this->getTablePrefix() . 'products.categoryID WHERE cat.categoryID IS NOT NULL AND cat.categoryID IN (' . $validCats . ')';
			//var_dump('test');			echo $this->productSql; exit;
		}

		if (!$data = $this->loadRecord($this->productSql))
		{
			return null;
		}

		$rec = Product::getNewInstance(Category::getInstanceById($this->getRealId('Category', $data['categoryID'])));
		$rec->setID($data['productID']);
		$rec->keywords->set($data['meta_keywords']);

		$rec->setValueByLang('name', $this->defLang, $data['name']);
		$rec->setValueByLang('longDescription', $this->defLang, $data['description']);
		$rec->setValueByLang('shortDescription', $this->defLang, $data['brief_description']);

		foreach ($this->attributes as $attr)
		{
			if (!empty($data['extrafield_' . $attr['optionID']]))
			{
				$rec->setAttributeValueByLang(SpecField::getInstanceByID($this->getRealId('SpecField', $attr['optionID']), SpecField::LOAD_DATA), $this->defLang, $data['extrafield_' . $attr['optionID']]);
			}
		}

		$data['voteSum'] = round($data['customers_rating'] * $data['customer_votes']);

		if ($data['product_code'])
		{
			$rec->sku->set($data['product_code']);
		}

		foreach (array(
					'shippingWeight' => 'weight',
					'stockCount' => 'in_stock',
					'shippingSurchargeAmount' => 'shipping_freight',
					'minimumQuantity' => 'min_order_amount',
					'dateCreated' => 'date_added',
					'ratingSum' => 'voteSum',
					'rating' => 'customers_rating',
					'ratingCount' => 'customer_votes',
				) as $lc => $xc)
		{
			$rec->$lc->set($data[$xc]);
		}

		$rec->isEnabled->set(1 == $data['enabled']);

		$rec->setPrice($this->getDefaultCurrency(), $data['Price']);

		if ($data['list_price'])
		{
			$price = $rec->getPricingHandler()->getPriceByCurrencyCode($this->getDefaultCurrency());
			$price->listPrice->set($data['list_price']);
		}

		// images
		foreach ($this->getDataBySQL('SELECT * FROM ' . $this->getTablePrefix() . 'product_pictures WHERE productID=' . $data['productID'] . ' ORDER BY (photoID=' . (int)$data['default_picture'] . '), photoID ASC') as $image)
		{
			$file = $image['enlarged'] ? $image['enlarged'] : $image['filename'];
			$this->importProductImage($rec, $this->path . '/products_pictures/' . $file);
		}

		$rec->rawData = $data;

		return $rec;
	}

	public function getNextCustomerOrder()
	{
		if (!$data = $this->loadRecord('SELECT * FROM ' . $this->getTablePrefix() . 'orders WHERE customerID > 0'))
		{
			return null;
		}

		$user = ActiveRecordModel::getInstanceByIDIfExists('User', $this->getRealId('User', $data['customerID'], false));
		if (!$user || !$user->isExistingRecord())
		{
			return $this->getNextCustomerOrder();
		}

		$currCode = $data['currency_code'];
		if (!$currency = ActiveRecordModel::getInstanceByIDIfExists('Currency', $currCode, false))
		{
			$currency = Currency::getNewInstance($currCode);
			$currency->save();
		}

		$order = CustomerOrder::getNewInstance($user);
		$order->currency->set($currency);
		$order->dateCompleted->set($data['order_time']);

		// products
		foreach ($this->getDataBySql('SELECT * FROM ' . $this->getTablePrefix() . 'ordered_carts WHERE orderID=' . $data['orderID']) as $item)
		{
			$product = null;

			// try to identify product by SKU
			preg_match('/\[(.*)\]/', $item['name'], $sku);
			if (isset($sku[1]))
			{
				$product = Product::getInstanceBySKU($sku[1]);
			}

			// if that doesn't work, then try to match the exact name
			if (!$product)
			{
				$productData = array_shift($this->getDataBySQL('SELECT productID FROM ' . $this->getTablePrefix() . 'products WHERE name="' . addslashes($item['name']) . '"'));
				if ($productData && is_array($productData))
				{
					$product = Product::getInstanceByID($this->getRealId('Product', $productData['productID']), Product::LOAD_DATA);
				}
			}

			if ($product)
			{
				$order->addProduct($product, $item['Quantity'], true);
				$orderedItem = array_shift($order->getItemsByProduct($product));
				$orderedItem->price->set($item['Price']);
			}
		}

		// addresses
		$order->shippingAddress->set($this->getUserAddress($data, 'shipping_'));
		$order->billingAddress->set($this->getUserAddress($data, 'billing_'));

		$order->status->set($this->getOrderStatus($data['statusID']));

		if ($order->status->get() == CustomerOrder::STATUS_SHIPPED)
		{
			$order->isPaid->set(true);
		}

		$order->rawData = $data;

		return $order;
	}

	private function getUserAddress($data, $prefix)
	{
		$address = UserAddress::getNewInstance();
		$map = array(
				'address' => 'address1',
				'city' => 'city',
				'zip' => 'postalCode',
				'state' => 'stateName',
				'first_name' => 'firstName',
				'last_name' => 'lastName',
			   );

		foreach ($map as $osc => $lc)
		{
			if (isset($data[$prefix . $osc]))
			{
				$address->$lc->set($data[$prefix . $osc]);
			}
		}

		$address->countryID->set($this->getCountryByName($data[$prefix . 'country']));

		return $address;
	}

	public function saveCustomerOrder(CustomerOrder $order)
	{
		$order->shippingAddress->get()->save();
		$order->billingAddress->get()->save();

		$order->save();

		$shipment = $order->getShipments()->get(0);
		if ($shipment)
		{
			$shipment->shippingAmount->set($order->rawData['shipping_cost']);
			if ($order->status->get() == CustomerOrder::STATUS_SHIPPED)
			{
				$shipment->status->set(Shipment::STATUS_SHIPPED);
			}
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

	protected function getConfigData()
	{
		$map = array();

		foreach ($this->getDataBySQL('SELECT * FROM ' . $this->getTablePrefix() . 'settings') as $row)
		{
			$map[$row['settings_constant_name']] = $row['settings_value'];
		}

		return $map;
	}

	private function getCountryByID($id)
	{
		if (!$this->countryMap)
		{
			foreach ($this->getDataBySQL('SELECT * FROM ' . $this->getTablePrefix() . 'countries') as $row)
			{
				$this->countryMap[$row['countryID']] = strtoupper($row['country_iso_2']);
			}
		}

		return $this->countryMap[$id];
	}

	private function getCountryByName($name)
	{
		if (!$this->countryNameMap)
		{
			foreach ($this->getDataBySQL('SELECT * FROM ' . $this->getTablePrefix() . 'countries') as $row)
			{
				$this->countryNameMap[$row['country_name']] = strtoupper($row['country_iso_2']);
			}
		}

		return $this->countryNameMap[$name];
	}

	private function getOrderStatus($shopScriptStatusCode)
	{
		if (!$this->statusMap)
		{
			// it seems that the status codes are editable by user, so we have to detect them by names
			$stringMap = array(
				'STRING_CANCELED_ORDER_STATUS' => null,
				'Pending' => CustomerOrder::STATUS_NEW,
				'Processing' => CustomerOrder::STATUS_PROCESSING,
				'Shipped' => CustomerOrder::STATUS_SHIPPED,
				'Delivered' => CustomerOrder::STATUS_SHIPPED,
				);

			$idMap = array(
				1 => null,
				2 => CustomerOrder::STATUS_NEW,
				3 => CustomerOrder::STATUS_PROCESSING,
				4 => CustomerOrder::STATUS_SHIPPED,
				5 => CustomerOrder::STATUS_SHIPPED,
				);

			foreach ($this->getDataBySQL('SELECT * FROM ' . $this->getTablePrefix() . 'order_status') as $row)
			{
				if (isset($stringMap[$row['status_name']]))
				{
					$this->statusMap[$row['statusID']] = $stringMap[$row['status_name']];
				}
				else if (isset($idMap[$row['statusID']]))
				{
					$this->statusMap[$row['statusID']] = $idMap[$row['statusID']];
				}
			}
		}

		return isset($this->statusMap[$shopScriptStatusCode]) ? $this->statusMap[$shopScriptStatusCode] : CustomerOrder::STATUS_PROCESSING;
	}
}

?>