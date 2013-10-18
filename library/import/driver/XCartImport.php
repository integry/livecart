<?php

require_once dirname(__file__) . '/../LiveCartImportDriver.php';

class XCartImport extends LiveCartImportDriver
{
	protected $categoryMap = null;

	protected $productSql;
	private $specFieldSql;

	private $languagesTruncated;
	private $currenciesTruncated;
	private $defLang;
	private $attributes = array();

	public function getName()
	{
		return 'X-Cart';
	}

	public function isPathValid()
	{
		// no path provided - won't be able to import images
		if (!$this->path)
		{
			return true;
		}

		foreach (array('catalog', 'giftcert.php', 'modules') as $file)
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

	public function isManufacturer()
	{
		return true;
	}

	public function isState()
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

	public function isSpecField()
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

	public function getVerificationTableNames()
	{
		return array('categories', 'orders', 'products', 'customers', 'states', 'extra_fields');
	}

	public function getTableMap()
	{
		return array(
				'Category' => $this->getTablePrefix() . 'categories',
				'CustomerOrder' => $this->getTablePrefix() . 'orders',
				'Language' => array('SELECT COUNT(DISTINCT code) FROM ' . $this->getTablePrefix() . 'languages' => $this->getTablePrefix() . 'languages'),
				'Manufacturer' => $this->getTablePrefix() . 'manufacturers',
				'Product' => $this->getTablePrefix() . 'products',
				'User' => $this->getTablePrefix() . 'customers',
				'State' => $this->getTablePrefix() . 'states',
				'SpecField' => $this->getTablePrefix() . 'extra_fields',
			);
	}

	public function getNextLanguage()
	{
		if (!$this->languagesTruncated)
		{
			ActiveRecord::getDbConnection()->executeQuery('TRUNCATE TABLE Language');
			$this->languagesTruncated = true;
		}

		if (!$data = $this->loadRecord('SELECT DISTINCT code FROM ' . $this->getTablePrefix() . 'languages'))
		{
			return null;
		}

		$isDefault = $this->getConfigValue('default_customer_language') == $data['code'];

		$code = $data['code'];
		if ('US' == $data['code'])
		{
			$data['code'] = 'en';
		}

		$data['code'] = strtolower($data['code']);

		if (!$isDefault)
		{
			$this->languages[$data['code']] = $code;
		}
		else
		{
			$this->defLang = $data['code'];
		}

		$lang = new Language;
		$lang->setID($data['code']);
		$lang->isEnabled->set(true);

		$lang->isDefault->set($isDefault);

		return $lang;
	}

	public function getNextManufacturer()
	{
		if (!$data = $this->loadRecord('SELECT * FROM ' . $this->getTablePrefix() . 'manufacturers'))
		{
			return null;
		}

		$man = new Manufacturer;
		$man->setID($data['manufacturerid']);
		$man->name->set($data['manufacturer']);

		return $man;
	}

	public function getNextState()
	{
		if (!$data = $this->loadRecord('SELECT * FROM ' . $this->getTablePrefix() . 'states'))
		{
			return null;
		}

		$rec = new State;

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

	public function getNextUser()
	{
		if (!$data = $this->loadRecord('SELECT * FROM ' . $this->getTablePrefix() . 'customers'))
		{
			return null;
		}

		$rec = User::getNewInstance($data['email']);

		foreach (array(
					'firstName' => 'firstname',
					'lastName' => 'lastname',
					'companyName' => 'company',
					'dateCreated' => 'first_login',
				) as $lc => $xc)
		{
			$rec->$lc->set($data[$xc]);
		}

		$rec->isEnabled->set($data['status'] == 'Y');

		if ($address = $this->getUserAddress($data, 's_'))
		{
			$rec->defaultShippingAddress->set(ShippingAddress::getNewInstance($rec, $address));
		}

		if ($address = $this->getUserAddress($data, 'b_'))
		{
			$rec->defaultBillingAddress->set(BillingAddress::getNewInstance($rec, $address));
		}

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
			$sql = 'SELECT ' . $this->getTablePrefix() . 'categories.* ' . ($langs ? ',' : '') . implode(', ', $langs) . ' FROM ' . $this->getTablePrefix() . 'categories ' . implode(' ', $join) . ' ORDER BY order_by ASC';
			foreach ($this->getDataBySQL($sql) as $category)
			{
				$this->categoryMap[$category['categoryid']] = $category;
			}

			// get level for each category
			foreach ($this->categoryMap as $id => &$category)
			{
				$level = 0;
				while ($id != 0 && ($level < 100))
				{
					$level++;

					if (isset($this->categoryMap[$id]['parentid']))
					{
						$id = $this->categoryMap[$id]['parentid'];
					}

					// parent category does not exist, so remove the category
					else if ($this->categoryMap[$id]['parentid'] != 0)
					{
						unset($this->categoryMap[$id]);
						$level = 101;
					}
				}

				// circular reference
				if ($level >= 100)
				{
					unset($this->categoryMap[$category['categoryid']]);
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
			$parentNode = 0 == $data['parentid'] ? Category::getRootNode() : Category::getInstanceById($this->getRealId('Category', $data['parentid']));
			$rec = Category::getNewInstance($parentNode);
		}
		else
		{
			return null;
		}

		$rec->setID($data['categoryid']);
		$rec->isEnabled->set('Y' == $data['avail']);
		$rec->keywords->set($data['meta_keywords']);

		$rec->setValueByLang('name', $this->defLang, $data['category']);
		$rec->setValueByLang('description', $this->defLang, $data['description']);

		foreach ($this->languages as $code => $id)
		{
			$rec->setValueByLang('name', $code, $data['category_' . $code]);
			$rec->setValueByLang('description', $code, $data['description_' . $code]);
		}

		//images
		$images = $this->getDataBySQL('SELECT * FROM ' . $this->getTablePrefix() . 'images_c WHERE id=' . $data['categoryid'] . ' ORDER BY orderby ASC');
		foreach ($images as $image)
		{
			$this->importCategoryImage($rec, $this->path . '/' . $image['image_path']);
		}

		$rec->rawData = $data;

		return $rec;
	}

	protected function joinCategoryFields($code, $id)
	{
		return array('LEFT JOIN ' . $this->getTablePrefix() . 'categories_lng AS category_' . $code . ' ON category_' . $code . '.categoryid=' . $this->getTablePrefix() . 'categories.categoryid AND category_' . $code . '.code="' . $id . '"',
					 'category_' . $code . '.category AS category_' . $code . ', category_' . $code . '.description AS description_' . $code
					);
	}

	public function getNextSpecField()
	{
		if (is_null($this->specFieldSql))
		{
			$join = $langs = array();
			foreach ($this->languages as $id => $code)
			{
				list($join[], $langs[]) = $this->joinAttributeFields($id, $code);
			}

			// get all categories
			$this->specFieldSql = 'SELECT ' . $this->getTablePrefix() . 'extra_fields.* ' . ($langs ? ',' : '') . implode(', ', $langs) . ' FROM ' . $this->getTablePrefix() . 'extra_fields ' . implode(' ', $join) . ' ORDER BY orderby ASC';
		}

		if (!$data = $this->loadRecord($this->specFieldSql))
		{
			return null;
		}

		$this->attributes[] = $data;

		$rec = SpecField::getNewInstance(Category::getRootNode(), SpecField::DATATYPE_TEXT, SpecField::TYPE_TEXT_SIMPLE);
		$rec->setID($data['fieldid']);
		$rec->handle->set($data['service_name']);
		$rec->setValueByLang('name', $this->defLang, $data['field']);
		$rec->isDisplayed->set(true);

		foreach ($this->languages as $code => $id)
		{
			$rec->setValueByLang('name', $code, $data['fieldname_' . $code]);
		}

		return $rec;
	}

	protected function joinAttributeFields($code, $id)
	{
		return array('LEFT JOIN ' . $this->getTablePrefix() . 'extra_fields_lng AS field_' . $code . ' ON field_' . $code . '.fieldid=' . $this->getTablePrefix() . 'extra_fields.fieldid AND field_' . $code . '.code="' . $id . '"',
					 'field_' . $code . '.field AS fieldname_' . $code
					);
	}

	public function getNextProduct()
	{
		if (!$this->productSql)
		{
			foreach ($this->languages as $code => $id)
			{
				list($join[], $langs[]) = $this->joinProductFields($id, $code);
			}

			foreach ($this->attributes as $attr)
			{
				$join[] = 'LEFT JOIN ' . $this->getTablePrefix() . 'extra_field_values  AS extra_' . $attr['fieldid'] . ' ON (extra_' . $attr['fieldid'] . '.productid=' . $this->getTablePrefix() . 'products.productid AND extra_' . $attr['fieldid'] . '.fieldid=' . $attr['fieldid'] . ')';
				$langs[] = 'extra_' . $attr['fieldid'] . '.value AS extrafield_' . $attr['fieldid'];
			}

			$this->productSql = 'SELECT ' . $this->getTablePrefix() . 'products.*, ' . $this->getTablePrefix() . 'products_categories.categoryid ' . ($langs ? ',' : '') . implode(', ', $langs) . ', (SELECT price FROM ' . $this->getTablePrefix() . 'pricing WHERE ' . $this->getTablePrefix() . 'pricing.productid=' . $this->getTablePrefix() . 'products.productid ORDER BY quantity ASC LIMIT 1) AS price FROM ' . $this->getTablePrefix() . 'products ' . implode(' ', $join) . ' LEFT JOIN ' . $this->getTablePrefix() . 'products_categories ON (' . $this->getTablePrefix() . 'products.productid=' . $this->getTablePrefix() . 'products_categories.productid AND ' . $this->getTablePrefix() . 'products_categories.main="Y")';
		}

		if (!$data = $this->loadRecord($this->productSql))
		{
			return null;
		}

		$rec = Product::getNewInstance(Category::getInstanceById($this->getRealId('Category', $data['categoryid'])));
		$rec->setID($data['productid']);
		$rec->keywords->set($data['keywords']);

		$rec->setValueByLang('name', $this->defLang, $data['product']);
		$rec->setValueByLang('longDescription', $this->defLang, $data['fulldescr']);
		$rec->setValueByLang('shortDescription', $this->defLang, $data['descr']);

		foreach ($this->languages as $code => $id)
		{
			$rec->setValueByLang('name', $code, $data['name_' . $code]);
			$rec->setValueByLang('longDescription', $code, $data['fulldescr_' . $code]);
			$rec->setValueByLang('shortDescription', $code, $data['descr_' . $code]);
		}

		foreach ($this->attributes as $attr)
		{
			if (!empty($data['extrafield_' . $attr['fieldid']]))
			{
				$rec->setAttributeValueByLang(SpecField::getInstanceByID($this->getRealId('SpecField', $attr['fieldid']), SpecField::LOAD_DATA), $this->defLang, $data['extrafield_' . $attr['fieldid']]);
			}
		}

		if ($data['manufacturerid'])
		{
			$rec->manufacturer->set(Manufacturer::getInstanceById($this->getRealId('Manufacturer', $data['manufacturerid'])));
		}

		foreach (array(
					'sku' => 'productcode',
					'shippingWeight' => 'weight',
					'stockCount' => 'avail',
					'shippingSurchargeAmount' => 'shipping_freight',
					'minimumQuantity' => 'min_amount',
					'dateCreated' => 'add_date',
				) as $lc => $xc)
		{
			$rec->$lc->set($data[$xc]);
		}

		$rec->isEnabled->set('Y' == $data['forsale']);

		$rec->setPrice($this->getDefaultCurrency(), $data['price']);

		//images
		$images = array_merge(
			// main thumbnail
			$this->getDataBySQL('SELECT * FROM ' . $this->getTablePrefix() . 'images_t WHERE id=' . $data['productid'] . ' ORDER BY orderby ASC'),

			// additional large size images
			$this->getDataBySQL('SELECT * FROM ' . $this->getTablePrefix() . 'images_d WHERE id=' . $data['productid'] . ' ORDER BY orderby ASC')
			);

		foreach ($images as $image)
		{
			$this->importProductImage($rec, $this->path . '/' . $image['image_path']);
		}

		$rec->rawData = $data;

		return $rec;
	}

	protected function joinProductFields($id, $code)
	{
		return array('LEFT JOIN ' . $this->getTablePrefix() . 'products_lng AS product_' . $code . ' ON (product_' . $code . '.productid=' . $this->getTablePrefix() . 'products.productid AND product_' . $code . '.code="' . $id . '")',
					 'product_' . $code . '.product AS name_' . $code . ', ' . 'product_' . $code . '.descr AS descr_' . $code . ', ' . 'product_' . $code . '.fulldescr AS fulldescr_' . $code
					);
	}

	public function getNextCustomerOrder()
	{
		if (!$data = $this->loadRecord('SELECT ' . $this->getTablePrefix() . 'orders.*, ' . $this->getTablePrefix() . 'customers.email AS userEmail FROM ' . $this->getTablePrefix() . 'orders LEFT JOIN ' . $this->getTablePrefix() . 'customers ON ' . $this->getTablePrefix() . 'orders.login=' . $this->getTablePrefix() . 'customers.login'))
		{
			return null;
		}

		if (!$user = User::getInstanceByEmail($data['userEmail']))
		{
			return $this->getNextCustomerOrder();
		}

		$order = CustomerOrder::getNewInstance($user);
		$order->currency->set(Currency::getInstanceById($this->getDefaultCurrency()));
		$order->dateCompleted->set($data['date']);

		// products
		foreach ($this->getDataBySql('SELECT * FROM ' . $this->getTablePrefix() . 'order_details WHERE orderid=' . $data['orderid']) as $prod)
		{
			try
			{
				$product = Product::getInstanceById($this->getRealId('Product', $prod['productid']), true);
				$order->addProduct($product, $prod['amount'], true);
				$item = array_shift($order->getItemsByProduct($product));
				$item->price->set($prod['price']);
			}
			catch (ARNotFoundException $e)
			{
				// the product no longer exists
			}
		}

		// addresses
		$order->shippingAddress->set($this->getUserAddress($data, 's_'));
		$order->billingAddress->set($this->getUserAddress($data, 'b_'));

		// assume that all orders are paid and shipped
		$order->status->set(CustomerOrder::STATUS_SHIPPED);
		$order->isPaid->set(true);

		$order->rawData = $data;

		return $order;
	}

	private function getUserAddress($data, $prefix)
	{
		$address = UserAddress::getNewInstance();
		$map = array(
				'company' => 'companyName',
				'address' => 'address1',
				'city' => 'city',
				'zipcode' => 'postalCode',
				'state' => 'stateName',
				'firstname' => 'firstName',
				'lastname' => 'lastName',
				'country' => 'countryID',
			   );

		$isData = false;
		foreach ($map as $osc => $lc)
		{
			if (isset($data[$prefix . $osc]))
			{
				$address->$lc->set($data[$prefix . $osc]);
				$isData = true;
			}
		}

		if (!$isData)
		{
			return null;
		}

		if (!empty($data['phone']))
		{
			$address->phone->set($data['phone']);
		}

		return $address;
	}

	public function saveCustomerOrder(CustomerOrder $order)
	{
		$order->shippingAddress->save();
		$order->billingAddress->save();

		$order->save();

		$shipment = $order->getShipments()->get(0);
		$shipment->shippingAmount->set($order->rawData['shipping_cost']);
		$shipment->save();

		if ($order->rawData['tax'] > 0)
		{
			$tax = new ShipmentTax;
			$tax->shipment->set($shipment);
			$tax->amount->set($order->rawData['tax']);
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
			if ($a['order_by'] == $b['order_by'])
			{
				return 0;
			}
			else
			{
				return $a['order_by'] > $b['order_by'] ? 1 : -1;
			}
		}

		return $a['level'] > $b['level'] ? 1 : -1;
	}

	protected function getConfigData()
	{
		$map = array();

		foreach ($this->getDataBySQL('SELECT * FROM ' . $this->getTablePrefix() . 'config') as $row)
		{
			$map[$row['name']] = $row['value'];
		}

		return $map;
	}
}

?>