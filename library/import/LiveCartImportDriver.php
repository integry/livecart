<?php

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
	const MAP_SIZE = 100;

	protected $db;
	protected $path;
	protected $importer;

	protected $languages = array();
	protected $configMap = null;

	private $recordMap = array();
	private $recordMapOffsets = array();
	private $fieldTests = array();
	private $defaultCurrencyCode;
	private $tablePrefix = null;

	public function __construct($dsn, $path = null)
	{
		$this->db = Creole::getConnection($dsn);
		$this->path = $path;
	}

	public abstract function getTableMap();

	protected abstract function getVerificationTableNames();

	public function setImporter(LiveCartImporter $importer)
	{
		$this->importer = $importer;
	}

	public function getDBInstance()
	{
		return $this->db;
	}

	public function isBillingAddress()
	{
		return false;
	}

	public function isCategory()
	{
		return false;
	}

	public function isCustomerOrder()
	{
		return false;
	}

	public function isCurrency()
	{
		return false;
	}

	public function isLanguage()
	{
		return false;
	}

	public function isProduct()
	{
		return false;
	}

	public function isManufacturer()
	{
		return false;
	}

	public function isNewsletterSubscriber()
	{
		return false;
	}

	public function isSpecField()
	{
		return false;
	}

	public function isState()
	{
		return false;
	}

	public function isStaticPage()
	{
		return false;
	}

	public function isUserGroup()
	{
		return false;
	}

	public function isUser()
	{
		return false;
	}

	public function isProductRelationship()
	{
		return false;
	}

	public function getNextLanguage()
	{
		return null;
	}

	public function getNextCurrency()
	{
		return null;
	}

	public function getNextManufacturer()
	{
		return null;
	}

	public function getNextUserGroup()
	{
		return null;
	}

	public function getNextUser()
	{
		return null;
	}

	public function getTotalRecordCount($type)
	{
		$tableMap = $this->getTableMap();
		if (isset($tableMap[$type]))
		{
			$sql = is_array($tableMap[$type]) ? key($tableMap[$type]) : 'SELECT COUNT(*) FROM `' . $tableMap[$type] . '`';
			try
			{
				return array_shift(array_shift($this->getDataBySQL($sql)));
			}
			catch (Exception $e)
			{
				return 0;
			}
		}
	}

	/**
	 *  Checks if the supplied database is valid
	 */
	public function isDatabaseValid()
	{
		$tables = array();

		// get database tables
		$info = $this->db->getDatabaseInfo();
		foreach ($info->getTables() as $table)
		{
			$tables[] = $table->getName();
		}

		foreach ($this->getVerificationTableNames() as $table)
		{
			$table = $this->getTablePrefix() . $table;

			if (array_search($table, $tables) === false)
			{
				return false;
			}
		}

		return true;
	}

	public function isPathValid()
	{
		return true;
	}

	public function getRealId($type, $id)
	{
		return $this->importer->getRealId($type, $id);
	}

	protected function getTablePrefix()
	{
		if (is_null($this->tablePrefix))
		{
			$dbinfo = $this->db->getDatabaseInfo();
			$tables = array();
			foreach($dbinfo->getTables() as $tbl)
			{
    			$tables[] = $tbl->getName();
    		}

			// find all possible prefixes
			$prefixes = array();
			foreach ($tables as $table)
			{
				foreach ($this->getVerificationTableNames() as $target)
				{
					if (strpos($table, $target) !== false)
					{
						$prefixes[substr($table, 0, strpos($table, $target))] = true;
					}
				}
			}

			// leave only valid prefixes
			foreach ($prefixes as $prefix => $foo)
			{
				foreach ($this->getVerificationTableNames() as $target)
				{
					if (array_search($prefix . $target, $tables) === false)
					{
						unset($prefixes[$prefix]);
					}
				}
			}

			reset($prefixes);
			$this->tablePrefix = key($prefixes);
		}

		return $this->tablePrefix;
	}

	protected function fieldExists($table, $field)
	{
		if (!isset($this->fieldTests[$table]))
		{
			$columns = array();
			foreach ($this->db->getDatabaseInfo()->getTable($this->getTablePrefix() . $table)->getColumns() as $column)
			{
				$columns[$column->getName()] = true;
			}

			$this->fieldTests[$table] = $columns;
		}

		return isset($this->fieldTests[$table][$field]);
	}

	protected function loadRecord($sql)
	{
		if (empty($this->recordMap[$sql]))
		{
			$this->recordMapOffset[$sql] = isset($this->recordMapOffset[$sql]) ? $this->recordMapOffset[$sql] + self::MAP_SIZE : 0;
			$this->recordMap[$sql] = $this->getDataBySQL($sql . ' LIMIT ' . $this->recordMapOffset[$sql] . ',' . self::MAP_SIZE);
		}

		if (!empty($this->recordMap[$sql]))
		{
			return array_shift($this->recordMap[$sql]);
		}
		else
		{
			return null;
		}
	}

	protected function getDataBySQL($sql)
	{
		$resultSet = $this->db->executeQuery($sql);
		$dataArray = array();
		while ($resultSet->next())
		{
			$dataArray[] = $resultSet->getRow();
		}

		return $dataArray;
	}

	protected function getConfigValue($key)
	{
		if (empty($this->configMap))
		{
			$this->configMap = $this->getConfigData();
		}

		if (isset($this->configMap[$key]))
		{
			return $this->configMap[$key];
		}
	}

	protected function addLanguage(Language $lang)
	{
		$this->languages[] = $lang;
	}

	protected function importProductImage(Product $product, $imagePath)
	{
		if (file_exists($imagePath) || (strtolower(substr($imagePath, 0, 7)) == 'http://'))
		{
			if (!isset($product->importedImages))
			{
				$product->importedImages = array();
			}
			$product->importedImages[] = $imagePath;
		}
	}

	protected function importCategoryImage(Category $category, $imagePath)
	{
		if (file_exists($imagePath))
		{
			if (!isset($category->importedImages))
			{
				$category->importedImages = array();
			}

			$category->importedImages[] = $imagePath;
		}
	}

	public function getDefaultCurrency()
	{
		if (!$this->defaultCurrencyCode)
		{
			$currency = array_shift(ActiveRecordModel::getRecordSetArray('Currency', new ARSelectFilter(new EqualsCond(new ARFieldHandle('Currency', 'isDefault'), true))));
			$this->defaultCurrencyCode = $currency['ID'];
		}

		return $this->defaultCurrencyCode;
	}

	public function saveProduct(Product $product)
	{
		/* @todo: figure out why it is necessary to explicitly mark the fields as modified to force saving */
		foreach ($product->getPricingHandler()->getPrices() as $price)
		{
			$price->product->set($product);
			$price->product->setAsModified();
			$price->currency->setAsModified();
			$price->price->setAsModified();
		}

		$product->save(ActiveRecord::PERFORM_INSERT);

		if (!empty($product->importedImages))
		{
			foreach ($product->importedImages as $imageFile)
			{
				$image = ProductImage::getNewInstance($product);
				$image->save();
				$image->setFile($imageFile);
				$image->__destruct();
			}

			unset($product->importedImages);
		}
	}

	public function saveCategory(Category $category)
	{
		$category->save(ActiveRecord::PERFORM_INSERT);

		if (!empty($category->importedImages))
		{
			foreach ($category->importedImages as $imageFile)
			{
				$image = CategoryImage::getNewInstance($category);
				$image->save();
				$image->setFile($imageFile);
			}
		}
	}

	public function saveCustomerOrder(CustomerOrder $order)
	{
		$order->isFinalized->set(true);
		$order->save();

		if ($order->shippingAddress->get())
		{
			$order->shippingAddress->get()->save();
		}

		if ($order->billingAddress->get())
		{
			$order->billingAddress->get()->save();
		}

		$order->totalAmount->set($order->calculateTotal($order->currency->get()));

		return $order->save();
	}

	public function saveState(State $state)
	{
		// make sure that such state doesn't exist already
		$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('State', 'countryID'), $state->countryID->get()));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('State', 'code'), $state->code->get()));
		if (!ActiveRecordModel::getRecordCount('State', $f))
		{
			return $state->save();
		}
	}

	public function saveUser(User $user)
	{
		$this->setUniqueEmail($user);
		$user->save(ActiveRecordModel::PERFORM_INSERT);

		if ($user->defaultBillingAddress->get())
		{
			$user->defaultBillingAddress->get()->save();
		}

		if ($user->defaultShippingAddress->get())
		{
			$user->defaultShippingAddress->get()->save();
		}
	}

	private function setUniqueEmail(User $user, $suffix = '')
	{
		$newEmail = $user->email->get() . $suffix;
		$existing = User::getInstanceByEmail($newEmail);
		if ($existing)
		{
			$this->setUniqueEmail($user, $suffix ? ++$suffix : 2);
		}
		else
		{
			$user->email->set($newEmail);
		}
	}
}

?>
