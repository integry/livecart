<?php

ClassLoader::import('application.model.product.ProductSet', true);
ClassLoader::import('application.model.product.ProductSpecification');
ClassLoader::import('application.model.product.ProductPricing');
ClassLoader::import('application.model.product.ProductImage');
ClassLoader::import('application.model.product.Manufacturer');
ClassLoader::import("application.model.system.Language");
ClassLoader::import("application.model.system.MultilingualObject");
ClassLoader::import("application.model.category.*");
ClassLoader::import("application.model.specification.*");
ClassLoader::import("application.model.product.*");

/**
 * One of the main entities of the system - defines and handles product related logic.
 * This class allows to assign or change product attribute values, product files, images, related products, etc.
 *
 * @package application.model.product
 * @author Integry Systems <http://integry.com>
 */
class Product extends MultilingualObject
{
	private static $multilingualFields = array("name", "shortDescription", "longDescription");

	private $specificationInstance = null;

	private $pricingHandlerInstance = null;

	const DO_NOT_RECALCULATE_PRICE = false;

	const TYPE_TANGIBLE = 0;
	const TYPE_DOWNLOADABLE = 1;
	const TYPE_BUNDLE = 2;

	const CHILD_OVERRIDE = 0;
	const CHILD_ADD = 1;
	const CHILD_SUBSTRACT = 2;

	/**
	 * Related products
	 * @return ARSet
	 */
	private $relationships = null;

	/**
	 * Removed relationships
	 * @return ARSet
	 */
	private $removedRelationships = null;

	private $bundledProducts = null;

	private $additionalCategories = null;

	private $variations = array();

	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("Product");

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("categoryID", "Category", "ID", null, ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("manufacturerID", "Manufacturer", "ID", null, ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("defaultImageID", "ProductImage", "ID", null, ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("parentID", "Product", "ID", null, ARInteger::instance()));

		$schema->registerField(new ARField("isEnabled", ARBool::instance()));
		$schema->registerField(new ARField("sku", ARVarchar::instance(20)));
		$schema->registerField(new ARField("name", ARArray::instance()));
		$schema->registerField(new ARField("shortDescription", ARArray::instance()));
		$schema->registerField(new ARField("longDescription", ARArray::instance()));
		$schema->registerField(new ARField("keywords", ARText::instance()));
		$schema->registerField(new ARField("pageTitle", ARArray::instance()));

		$schema->registerField(new ARField("dateCreated", ARDateTime::instance()));
		$schema->registerField(new ARField("dateUpdated", ARDateTime::instance()));

		$schema->registerField(new ARField("URL", ARVarchar::instance(256)));
		$schema->registerField(new ARField("isFeatured", ARBool::instance()));
		$schema->registerField(new ARField("type", ARInteger::instance(4)));

		$schema->registerField(new ArField("ratingSum", ARInteger::instance()));
		$schema->registerField(new ArField("ratingCount", ARInteger::instance()));
		$schema->registerField(new ArField("rating", ARFloat::instance(8)));
		$schema->registerField(new ArField("reviewCount", ARInteger::instance()));

		$schema->registerField(new ArField("minimumQuantity", ARFloat::instance(8)));
		$schema->registerField(new ArField("shippingSurchargeAmount", ARFloat::instance(8)));
		$schema->registerField(new ArField("isSeparateShipment", ARBool::instance()));
		$schema->registerField(new ArField("isFreeShipping", ARBool::instance()));
		$schema->registerField(new ArField("isBackOrderable", ARBool::instance()));
		$schema->registerField(new ArField("isFractionalUnit", ARBool::instance()));
		$schema->registerField(new ArField("isUnlimitedStock", ARBool::instance()));

		$schema->registerField(new ArField("shippingWeight", ARFloat::instance(8)));

		$schema->registerField(new ArField("stockCount", ARFloat::instance(8)));
		$schema->registerField(new ArField("reservedCount", ARFloat::instance(8)));
		$schema->registerField(new ArField("salesRank", ARInteger::instance()));
		$schema->registerField(new ArField("childSettings", ARText::instance()));
		$schema->registerField(new ArField("fractionalStep", ARFloat::instance(8)));
		$schema->registerField(new ArField("position", ARInteger::instance()));
	}

	/**
	 * Creates a new product instance
	 *
	 * @param Category $category
	 *
	 * @return Product
	 */
	public static function getNewInstance(Category $category, $name = '')
	{
		$product = parent::getNewInstance(__CLASS__);
		$product->category->set($category);
		$product->setValueByLang('name', null, $name);

		return $product;
	}

	/**
	 * Get product active record instance
	 *
	 * @param mixed $recordID
	 * @param bool $loadRecordData
	 * @param bool $loadReferencedRecords
	 *
	 * @return Product
	 */
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false)
	{
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords);
	}

	/**
	 * Get product instance by SKU
	 *
	 * @param mixed $sku
	 * @param bool $loadReferencedRecords
	 *
	 * @return Product
	 */
	public static function getInstanceBySKU($sku, $loadReferencedRecords = false)
	{
		$f = new ARSelectFilter();
		$f->setCondition(new EqualsCond(new ARFieldHandle('Product', 'sku'), $sku));
		$f->setLimit(1);

		$set = self::getRecordSet($f, $loadReferencedRecords);
		if (!$set->size())
		{
			return false;
		}
		else
		{
			return $set->get(0);
		}
	}

	/**
	 * Get products record set
	 *
	 * @param ARSelectFilter $filter
	 * @param bool $loadReferencedRecords
	 *
	 * @return ARSet
	 */
	public static function getRecordSet(ARSelectFilter $filter, $loadReferencedRecords = false)
	{
		return parent::getRecordSet(__CLASS__, $filter, $loadReferencedRecords);
	}

	/*####################  Value retrieval and manipulation ####################*/

	public function createChildProduct()
	{
		$child = ActiveRecord::getNewInstance(__CLASS__);
		$child->parent->set($this);
		return $child;
	}

	public function getChildSetting($setting)
	{
		$settings = unserialize($this->childSettings->get());
		if (isset($settings[$setting]))
		{
			return $settings[$setting];
		}
	}

	public function setChildSetting($setting, $value)
	{
		$settings = unserialize($this->childSettings->get());
		$settings[$setting] = $value;
		$this->childSettings->set(serialize($settings));
	}

	public function belongsTo(Category $category)
	{
		$belongsTo = $category->isAncestorOf($this->getCategory());

		if (!$belongsTo && $this->additionalCategories)
		{
			foreach ($this->additionalCategories as $cat)
			{
				if ($category->isAncestorOf($cat))
				{
					return true;
				}
			}
		}

		return $belongsTo;
	}

	public function isRelatedTo(Product $product, $type)
	{
		return ProductRelationship::hasRelationship($product, $this, $type);
	}

	/**
	 *  Check if the product is available for purchasing
	 */
	public function isAvailable($requireEnabled = true)
	{
		if (!$this->isLoaded())
		{
			$this->load();
		}

		if (!$this->isBundle())
		{
			return self::isAvailableForOrdering($this->isEnabled->get() || !$requireEnabled, $this->stockCount->get(), $this->isBackOrderable->get(),  $this->isUnlimitedStock->get(), $this->type->get());
		}
		else
		{
			if (!$this->isEnabled->get())
			{
				return false;
			}

			foreach ($this->getBundledProducts() as $item)
			{
				if (!$item->relatedProduct->get()->isAvailable(false))
				{
					return false;
				}
			}

			return true;
		}
	}

	public function isInventoryTracked($type = null, $isUnlimitedStock = null)
	{
		if (!empty($this))
		{
			foreach (array('type', 'isUnlimitedStock') as $var)
			{
				if (is_null($$var))
				{
					$$var = $this->$var->get();
				}
			}
		}

		$config = self::getApplication()->getConfig();

		if ($isUnlimitedStock || ($config->get('INVENTORY_TRACKING') == 'DISABLE'))
		{
			return false;
		}

		if ($type != self::TYPE_DOWNLOADABLE)
		{
			return true;
		}
		else
		{
			return $config->get('INVENTORY_TRACKING_DOWNLOADABLE');
		}
	}

	/**
	 *  Determines if the product is downloadable (digital file) - as opposed to shippable products
	 */
	public function isDownloadable()
	{
		if (!$this->isBundle())
		{
			return $this->type->get() == self::TYPE_DOWNLOADABLE;
		}
		else
		{
			$isDownloadable = true;
			foreach ($this->getBundledProducts() as $product)
			{
				if (!$product->relatedProduct->get()->isDownloadable())
				{
					$isDownloadable = false;
					break;
				}
			}

			return $isDownloadable;
		}
	}

	/**
	 *  Determines if the product is a bundle (container of other products)
	 */
	public function isBundle()
	{
		return $this->type->get() == self::TYPE_BUNDLE;
	}

	protected static function isAvailableForOrdering($isEnabled, $stockCount, $isBackOrderable, $isUnlimitedStock, $type)
	{
		if ($isEnabled)
		{
			$config = self::getApplication()->getConfig();

			if (!self::isInventoryTracked($type, $isUnlimitedStock))
			{
				return true;
			}

			if (!$stockCount && !$isBackOrderable)
			{
				return false;
			}
			else
			{
				return true;
			}
		}
		else
		{
			return false;
		}
	}

	public function getMaxOrderableCount()
	{
		$config = self::getApplication()->getConfig();

		if (!$this->isInventoryTracked() || $this->isBackOrderable->get())
		{
			return null;
		}

		return $this->stockCount->get();
	}

	private function loadPricingFromRequest(Request $request, $listPrice = false)
	{
		$field = $listPrice ? 'listPrice' : 'price';

		$currencies = self::getApplication()->getCurrencyArray();
		foreach ($currencies as $currency)
		{
			$price = $request->get($field . '_' . $currency);
			if (strlen($price))
			{
				$this->setPrice($currency, $price, $listPrice);
			}
			else if ($request->isValueSet($field . '_' . $currency))
			{
				$this->getPricingHandler()->removePriceByCurrencyCode($currency, $listPrice);
			}
		}
	}

	public function loadRequestData(Request $request)
	{
		// basic data
		parent::loadRequestData($request);

	  	if(!$this->isExistingRecord())
		{
			$this->save();
		}

		// set manufacturer
		if ($request->isValueSet('manufacturer'))
		{
			$this->manufacturer->set(Manufacturer::getInstanceByName($request->get('manufacturer')));
		}

		$this->getSpecification()->loadRequestData($request);

		// set prices
		$this->loadPricingFromRequest($request);
		$this->loadPricingFromRequest($request, true);
	}

	/**
	 * Sets specification attribute
	 *
	 * @param iEavSpecification $specification Specification item value
	 */
	public function setAttribute(iEavSpecification $specification)
	{
		$this->getSpecification()->setAttribute($specification);
	}

	/**
	 * Sets specification attribute value
	 *
	 * @param SpecField $field Specification field instance
	 * @param mixed $value Attribute value
	 */
	public function setAttributeValue(SpecField $field, $value)
	{
		$this->getSpecification()->setAttributeValue($field, $value);
	}

	/**
	 * Sets specification String attribute value by language
	 *
	 * @param SpecField $field Specification field instance
	 * @param unknown $value Attribute value
	 */
	public function setAttributeValueByLang(SpecField $field, $langCode, $value)
	{
		$this->getSpecification()->setAttributeValueByLang($field, $langCode, $value);
	}

	/**
	 * Removes persisted product specification property
	 *
	 *	@param SpecField $field SpecField instance
	 */
	public function removeAttribute(SpecField $field)
	{
		$this->getSpecification()->removeAttribute($field);
	}

	public function removeAttributeValue(SpecField $field, SpecFieldValue $value)
	{
		$this->getSpecification()->removeAttributeValue($field, $value);
	}

	/**
	 * Gets a product specification instance
	 *
	 * @return ProductSpecification
	 */
	public function getSpecification()
	{
		if (!$this->specificationInstance)
		{
			//$this->specificationInstance = new ProductSpecification($this);
			$this->loadSpecification();
		}

		return $this->specificationInstance;
	}

	/**
	 * Gets a product pricing handler instance
	 *
	 * @return ProductPricing
	 */
	public function getPricingHandler()
	{
		if (!$this->pricingHandlerInstance)
		{
			$this->pricingHandlerInstance = new ProductPricing($this, null, self::getApplication());
		}

		return $this->pricingHandlerInstance;
	}

	public function isPricingLoaded()
	{
		return !is_null($this->pricingHandlerInstance);
	}

	public function setPrice($currencyCode, $price, $listPrice = false)
	{
	  	$instance = $this->getPricingHandler()->getPriceByCurrencyCode($currencyCode);

	  	if (strlen($price) == 0 && !$listPrice)
	  	{
	  		$this->getPricingHandler()->removePriceByCurrencyCode($currencyCode);
		}
		else
		{
			$instance->setFieldValue($listPrice ? 'listPrice' : 'price', $price);
		}

		$this->getPricingHandler()->setPrice($instance);
	}

	public function getItemPrice(OrderedItem $item, $applyRounding = true, Currency $currency = null)
	{
		$currency = $currency ? $currency : $item->getCurrency();
		$currencyCode = $currency->getID();
		return $this->getPricingHandler()->getPriceByCurrencyCode($currencyCode)->getItemPrice($item, $applyRounding);
	}

	public function getPrice($currencyCode, $recalculate = true, $includeDiscounts = false)
	{
	  	if ($currencyCode instanceof Currency)
	  	{
			$currencyCode = $currencyCode->getID();
		}

		$instance = $this->getPricingHandler()->getPriceByCurrencyCode($currencyCode);
	  	if (!$instance->getPrice() && $recalculate)
	  	{
	  		$price = $instance->reCalculatePrice();
		}
		else
		{
			$price = $instance->getPrice($recalculate == true, $includeDiscounts);
		}

		return $price;
	}

	public function addRelatedProduct(Product $product, $type = 0)
	{
		$relationship = ProductRelationship::getNewInstance($this, $product);
		$relationship->type->set($type);
		$this->getRelationships($type)->add($relationship);
		$this->getRemovedRelationships()->removeRecord($relationship);
	}

	public function removeFromRelatedProducts(Product $product, $type)
	{
		$this->getRelationships($type);
		$relationship = ProductRelationship::getInstance($this, $product, $type);

		$this->relationships[$type]->removeRecord($relationship);

		$this->getRemovedRelationships()->add($relationship);
	}

	public function markAsNotLoaded()
	{
		parent::markAsNotLoaded();
		$this->relationships = null;
	}

	public function getShippingWeight()
	{
		if (!$this->isBundle())
		{
			if (!$this->isDownloadable())
			{
				if ($this->parent->get())
				{
					$parentWeight = $this->parent->get()->getShippingWeight();
					$weight = $this->shippingWeight->get();

					if ($this->getChildSetting('weight') == Product::CHILD_ADD)
					{
						return $parentWeight + $weight;
					}
					else if ($this->getChildSetting('weight') == Product::CHILD_SUBSTRACT)
					{
						return $parentWeight - $weight;
					}
					else if ($weight > 0)
					{
						return $weight;
					}
					else
					{
						return $parentWeight;
					}
				}
				else
				{
					return $this->shippingWeight->get();
				}
			}
		}
		else
		{
			$weight = 0;
			foreach ($this->getBundledProducts() as $item)
			{
				$weight += $item->relatedProduct->get()->getShippingWeight();
			}

			return $weight;
		}
	}

	/*####################  Saving ####################*/

	public function getCountUpdateFilter($isDeleting = false)
	{
		$sign = $isDeleting ? '-' : '+';

		// update category product count numbers
		$catUpdate = new ARUpdateFilter();

		$catUpdate->addModifier('totalProductCount', new ARExpressionHandle('totalProductCount ' . $sign . ' 1'));

		if ($this->isEnabled->get())
		{
			$catUpdate->addModifier('activeProductCount', new ARExpressionHandle('activeProductCount ' . $sign . ' 1'));

			if ($this->stockCount->get() > 0)
			{
				$catUpdate->addModifier('availableProductCount', new ARExpressionHandle('availableProductCount ' . $sign . ' 1'));
			}
		}

		return $catUpdate;
	}

	/**
	 * Inserts new product record to a database
	 *
	 */
	protected function insert()
	{
		ActiveRecordModel::beginTransaction();

		try
		{
			$this->dateCreated->set(new ARSerializableDateTime());
			$this->dateUpdated->set(new ARSerializableDateTime());

			parent::insert();

			if ($this->category->get())
			{
				$this->updateCategoryCounters($this->getCountUpdateFilter(), $this->category->get());
			}

			ActiveRecordModel::commit();
		}
		catch (Exception $e)
		{
			ActiveRecordModel::rollback();
			throw $e;
		}
	}

	/**
	 * Updates product record
	 *
	 */
	protected function update()
	{
		ActiveRecordModel::beginTransaction();

		try
		{
			// modify product counters for categories
			$catUpdate = new ARUpdateFilter();

			// determines the changes for activeProductCount and availableProductCount fields
			$activeChange = 0;
			$availableChange = 0;

			// when isEnabled flag is modified the activeProductCount will always either increase or decrease
			if ($this->isEnabled->isModified())
			{
				$activeChange = $this->isEnabled->get() ? 1 : -1;

				// when the stock count is larger than 0, the availableProductCount should also change by one
				if ($this->isDownloadable() || (!$this->stockCount->isModified() && $this->stockCount->get() > 0))
				{
					$availableChange = $this->isEnabled->get() ? 1 : -1;
				}
			}

			if ($this->stockCount->isModified() && $this->isEnabled->get())
			{
				// decrease available product count
				if ($this->stockCount->get() == 0 && $this->stockCount->getInitialValue() > 0)
				{
					$availableChange = -1;
				}

				// increase available product count
				else if ($this->stockCount->get() > 0 && $this->stockCount->getInitialValue() == 0)
				{
					$availableChange = 1;
				}
			}

			if ($activeChange != 0)
			{
				$catUpdate->addModifier('activeProductCount', new ARExpressionHandle('activeProductCount ' . (($activeChange > 0) ? '+' : '-') . ' 1'));
			}

			if ($availableChange != 0)
			{
				$catUpdate->addModifier('availableProductCount', new ARExpressionHandle('availableProductCount ' . (($availableChange > 0) ? '+' : '-') . ' 1'));
			}

			parent::update();

			if (!$this->isLoaded())
			{
				$this->load(array('Category'));
			}

			if ($this->category->get())
			{
				$this->updateCategoryCounters($catUpdate, $this->category->get());
			}

			$update = new ARUpdateFilter();
			$update->addModifier('dateUpdated', new ARExpressionHandle('NOW()'));
			$update->setCondition(new EqualsCond(new ARFieldHandle(__CLASS__, 'ID'), $this->getID()));
			ActiveRecordModel::updateRecordSet(__CLASS__, $update);

			ActiveRecordModel::commit();
		}
		catch (Exception $e)
		{
			ActiveRecordModel::rollback();
			throw $e;
		}
	}

	/**
	 *  @todo move the SKU checking to insert() - otherwise seems to break some tests for now
	 */
	public function save($forceOperation = null)
	{
		self::beginTransaction();

		if ($this->manufacturer->get())
		{
			$this->manufacturer->get()->save();
		}

		// update parent inventory counter
		if ($this->stockCount->isModified() && $this->parent->get())
		{
			$stockDifference = $this->stockCount->get() - $this->stockCount->getInitialValue();

			$this->parent->get()->stockCount->set($this->parent->get()->stockCount->get() + $stockDifference);
			$this->parent->get()->save();
		}

		parent::save($forceOperation);

		// generate SKU automatically if not set
		if (!$this->sku->get())
		{
			ClassLoader::import('application.helper.check.IsUniqueSkuCheck');

			if (!$this->parent->get())
			{
				$sku = $this->getID();

				do
				{
					$check = new IsUniqueSkuCheck('', $this);
					$exists = $check->isValid('SKU' . $sku);
					if (!$exists)
					{
						$sku = '0' . $sku;
					}
				}
				while (!$exists);

				$sku = 'SKU' . $sku;
			}
			else
			{
				$sku = $this->parent->get()->sku->get() . '-';

				$k = 0;
				do
				{
					$k++;
					$check = new IsUniqueSkuCheck('', $this);
					$exists = $check->isValid($sku . $k);
				}
				while (!$exists);

				$sku .= $k;
			}

			$this->sku->set($sku);
			parent::save();
		}

		$this->getSpecification()->save();
		$this->getPricingHandler()->save();
		$this->saveRelationships();

		self::commit();
	}

	/**
	 * Removes a product from a database
	 *
	 * @param int $recordID
	 * @return bool
	 * @throws Exception
	 */
	public static function deleteByID($recordID)
	{
		ActiveRecordModel::beginTransaction();
		try
		{
			$product = Product::getInstanceByID($recordID, Product::LOAD_DATA);

			$filter = $product->getCountUpdateFilter(true);
			$product->updateCategoryCounters($filter, $product->category->get());

			foreach ($product->getAdditionalCategories() as $category)
			{
				$product->updateCategoryCounters($filter, $category);
			}

			parent::deleteByID(__CLASS__, $recordID);
			ActiveRecordModel::commit();
			return true;
		}
		catch (Exception $e)
		{
			ActiveRecordModel::rollback();
			throw $e;
		}
	}

	public function delete()
	{
		return self::deleteByID($this->getID());
	}

	public function updateCategoryCounters(ARUpdateFilter $catUpdate, Category $category)
	{
		if ($catUpdate->isModifierSet())
		{
			$categoryPathNodes = $category->getPathNodeArray(Category::INCLUDE_ROOT_NODE);
			$catIDs = array();
			foreach ($categoryPathNodes as $node)
			{
				$catIDs[] = $node['ID'];
			}
			$catIDs[] = $category->getID();

			$catUpdate->setCondition(new INCond(new ARFieldHandle('Category', 'ID'), $catIDs));

			ActiveRecordModel::updateRecordSet('Category', $catUpdate);
		}
	}

	public function saveRelationships()
	{
		foreach($this->getRemovedRelationships() as $relationship)
		{
			$relationship->delete();
		}

		if (is_null($this->relationships))
		{
			return;
		}

		foreach($this->relationships as $type => $relationships)
		{
			foreach ($relationships as $relationship)
			{
				$relationship->save();
			}
		}
	}

	/*####################  Data array transformation ####################*/

	public static function sortAttributesByHandle(&$array)
	{
		return ProductSpecification::sortAttributesByHandle('ProductSpecification', $array);
	}

	public function toArray()
	{
	  	$array = parent::toArray();
	  	if ($this->isLoaded())
	  	{
			$array['attributes'] = $this->getSpecification()->toArray();
			self::sortAttributesByHandle($array);
			$array = array_merge($array, $this->getPricesFields());
		}

		if ($this->parent->get())
		{
			$array = array_merge($this->parent->get()->toArray(), array_filter($array));
		}

		foreach ($this->variations as $variation)
		{
			$array['variations'][$variation->getID()] = $variation->toArray();
		}

		$this->setArrayData($array);

	  	return $array;
	}

	public static function transformArray($array, ARSchema $schema)
	{
		$array = parent::transformArray($array, $schema);

		$array['isTangible'] = $array['type'] == self::TYPE_TANGIBLE;
		$array['isDownloadable'] = $array['type'] == self::TYPE_DOWNLOADABLE;
		$array['isInventoryTracked'] = self::isInventoryTracked($array['type'], $array['isUnlimitedStock']);

		if ($array['isEnabled'])
		{
			if ($array['isDownloadable'])
			{
				$array['isAvailable'] = true;
			}
			else
			{
				$array['isAvailable'] = self::isAvailableForOrdering($array['isEnabled'], $array['stockCount'], $array['isBackOrderable'], $array['isUnlimitedStock'], $array['type']);
			}
		}
		else
		{
			$array['isAvailable'] = false;
		}

		if ($array['childSettings'])
		{
			$array['childSettings'] = unserialize($array['childSettings']);
		}

		return $array;
	}

	/*####################  Get related objects ####################*/

	public function getParent()
	{
		return $this->parent->get() ? $this->parent->get() : $this;
	}

	public function getCategory()
	{
		$parent = $this->getParent();

		if (!$parent->isLoaded())
		{
			$parent->load(array('Category', 'ProductImage'));
		}

		return $parent->category->get();
	}

	public function getParentValue($field)
	{
		foreach (array($this, $this->parent->get()) as $rec)
		{
			if ($rec && $rec->$field->get())
			{
				return $rec->$field->get();
			}
		}

		return $this->$field->get();
	}

	public function getName($languageCode = null)
	{
		$parent = $this->getParent();
		$parent->load();

		foreach (array($parent, $this) as $product)
		{
			if ($name = $product->getValueByLang('name', $languageCode))
			{
				return $name;
			}
		}
	}

	public function getMinimumQuantity()
	{
		$quant = $this->minimumQuantity->get();
		if ($step = $this->fractionalStep->get())
		{
			$quant = floor($quant / $step) * $step;
			if (!$quant)
			{
				$quant = $step;
			}
		}

		if (!$quant)
		{
			$quant = 1;
		}

		return $quant;
	}

	public function getQuantityStep()
	{
		if ($this->fractionalStep->get())
		{
			return $this->fractionalStep->get();
		}
		else
		{
			return 1;
		}
	}

	public function loadSpecification($specificationData = null)
	{
	  	if ($this->specificationInstance)
	  	{
	  		return false;
		}

		$this->specificationInstance = new ProductSpecification($this, $specificationData);
	}

	public function loadPricing($pricingData = null)
	{
		$this->pricingHandlerInstance = new ProductPricing($this, $pricingData, self::getApplication());
	}

	public function getPricesFields()
	{
		$prices = $this->getPricingHandler()->toArray();
	  	foreach($prices['calculated'] as $code => $value)
	  	{
	  		$prices['price_' . $code] = $value;
	  	}

	  	$listPrices = $this->getPricingHandler()->toArray(ProductPricing::BOTH, ProductPricing::LIST_PRICE);
	  	foreach($listPrices['calculated'] as $code => $value)
	  	{
	  		if ($value > 0)
	  		{
	  			$prices['listPrice_' . $code] = $value;
	  		}
	  	}

	  	$prices['formattedListPrice'] = $listPrices['formattedPrice'];

	  	return $prices;
	}

	/**
	 *
	 */
	public function getImageArray()
	{
		$f = new ARSelectFilter();
		$f->setCondition(new EqualsCond(new ARFieldHandle('ProductImage', 'productID'), $this->getID()));
		$f->setOrder(new ARFieldHandle('ProductImage', 'position'));

		return ActiveRecordModel::getRecordSetArray('ProductImage', $f);
	}

	/**
	 * Count products in category
	 *
	 * @param Category $category Category active record
	 * @return integer
	 */
	public static function countItems(Category $category)
	{
		return $category->getProductSet(new ARSelectFilter(), false)->getTotalRecordCount();
	}

	public function loadRelationships($loadReferencedRecords = false, $type = 0)
	{
		ClassLoader::import('application.model.product.ProductRelationship');
		if (empty($this->relationships[$type]))
		{
			$this->relationships[$type] = ProductRelationship::getRelationships($this, $loadReferencedRecords, $type);
		}

		return $this->relationships[$type];
	}

	/**
	 * @return ARSet
	 */
	public function getRelationships($type = 0, $loadReferencedRecords = array('RelatedProduct' => 'Product', 'DefaultImage' => 'ProductImage', 'Manufacturer', 'ProductRelationshipGroup'))
	{
		return $this->loadRelationships($loadReferencedRecords, $type);
	}

	public function getAllCategories()
	{
		$set = new ARSet();
		$set->add($this->getCategory());

		if ($additional = $this->getAdditionalCategories())
		{
			foreach ($additional as $cat)
			{
				$set->add($cat);
			}
		}

		return $set;
	}

	public function getAdditionalCategories()
	{
		if (is_null($this->additionalCategories))
		{
			$this->additionalCategories = array();

			ClassLoader::import('application.model.category.ProductCategory');

			$categories = new ARSet();
			$filter = new ARSelectFilter();
			$filter->setOrder(new ARFieldHandle('Category', 'lft'));

			foreach ($this->getParent()->getRelatedRecordSet('ProductCategory', $filter, array('Category')) as $productCat)
			{
				$this->registerAdditionalCategory($productCat->category->get());
			}
		}

		return $this->additionalCategories;
	}

	public function loadAdditionalCategoriesForSet(ARSet $set)
	{
		$map = $set->getIDMap();
		foreach (ActiveRecordModel::getRecordSet('ProductCategory', new ARSelectFilter(new INCond(new ARFieldHandle('ProductCategory', 'productID'), $set->getRecordIDs())), array('Category')) as $additional)
		{
			$map[$additional->product->get()->getID()]->registerAdditionalCategory($additional->category->get());
		}
	}

	public function registerAdditionalCategory(Category $category)
	{
		$this->additionalCategories[$category->getID()] = $category;
	}

	public function registerVariation(ProductVariation $variation)
	{
		$this->variations[$variation->getID()] = $variation;
	}

	/**
	 * @return ARSet
	 */
	public function getRelationshipsArray($type, $loadReferencedRecords = array('RelatedProduct' => 'Product', 'DefaultImage' => 'ProductImage', 'Manufacturer', 'ProductRelationshipGroup', 'Category'))
	{
		ClassLoader::import('application.model.product.ProductRelationship');
		return ProductRelationship::getRelationshipsArray($this, $loadReferencedRecords, $type);
	}

	/**
	 * @return ARSet
	 */
	public function getRelatedProducts($type = 0)
	{
		$relatedProducts = new ARSet();

		foreach($this->getRelationships($type) as $relationship)
		{
			$relatedProducts->add($relationship->relatedProduct->get());
		}
		return $relatedProducts;
	}

	private function getRemovedRelationships()
	{
		if(is_null($this->removedRelationships)) $this->removedRelationships = new ARSet();

		return $this->removedRelationships;
	}

	/**
	 * @return ARSet
	 */
	public function getRelationshipGroups($type)
	{
		ClassLoader::import('application.model.product.ProductRelationshipGroup');
		return ProductRelationshipGroup::getProductGroups($this, $type);
	}

	/**
	 * @return array
	 */
	public function getRelationshipGroupArray($type = 0)
	{
		ClassLoader::import('application.model.product.ProductRelationshipGroup');
		return ProductRelationshipGroup::getProductGroupArray($this, $type);
	}

	public function getRelatedProductsWithGroupsArray($type = 0)
	{
		ClassLoader::import('application.model.product.ProductRelationshipGroup');
		return ProductRelationshipGroup::mergeGroupsWithFields($this->getRelationshipGroupArray($type), $this->getRelationshipsArray($type));
	}

	/**
	 * @return ARSet
	 */
	public function getFileGroups()
	{
		ClassLoader::import('application.model.product.ProductFileGroup');
		return ProductFileGroup::getProductGroups($this);
	}

	/**
	 * @return ARSet
	 */
	public function getFiles()
	{
		ClassLoader::import('application.model.product.ProductFile');
		return ProductFile::getFilesByProduct($this);
	}

	public function getFilesMergedWithGroupsArray()
	{
		ClassLoader::import('application.model.product.ProductFileGroup');
		return ProductFileGroup::mergeGroupsWithFields($this->getFileGroups()->toArray(), $this->getFiles()->toArray());
	}

	public function getOptions($includeInheritedOptions = false)
	{
		$parent = $this->getParent();
		ClassLoader::import('application.model.product.ProductOption');
		$f = new ARSelectFilter();
		$f->setOrder(new ARFieldHandle('ProductOption', 'position'), 'ASC');
		$options = $parent->getRelatedRecordSet('ProductOption', $f, array('DefaultChoice' => 'ProductOptionChoice'));

		if ($includeInheritedOptions)
		{
			$options->merge($parent->getCategory()->getOptions(true));

			foreach ($parent->getAdditionalCategories() as $cat)
			{
				$options->merge($cat->getOptions(true));
			}

			ProductOption::loadChoicesForRecordSet($options);

			foreach ($options as $mainIndex => $mainOption)
			{
				for ($k = $mainIndex + 1; $k <= $options->size(); $k++)
				{
					if ($options->get($k) && ($mainOption->getID() == $options->get($k)->getID()))
					{
						$options->remove($k);
					}
				}
			}
		}

		return $options;
	}

	public function getOptionsArray()
	{
		$parent = $this->getParent();
		$options = $parent->getOptions(true)->toArray();
		ProductOption::includeProductPrice($parent, $options);

		return $options;
	}

	public function getProductsPurchasedTogether($limit = null, $enabledOnly = false)
	{
		if (0 === $limit)
		{
			return array();
		}

		if (is_null($limit))
		{
			$limit = 0;
		}

		$sql = 'SELECT
					COUNT(*) AS cnt, OtherItem.productID AS ID FROM OrderedItem
				LEFT JOIN
					CustomerOrder ON OrderedItem.customerOrderID=CustomerOrder.ID
				LEFT JOIN
					OrderedItem AS OtherItem ON OtherItem.customerOrderID=CustomerOrder.ID
				LEFT JOIN
					Product ON OtherItem.productID=Product.ID
				WHERE
					CustomerOrder.isFinalized=1 AND OrderedItem.productID=' . $this->getID() . ' AND OtherItem.productID!=' . $this->getID() . ($enabledOnly? ' AND Product.isEnabled=1' : '') . '
				GROUP
					BY OtherItem.productID
				ORDER BY
					cnt DESC
				LIMIT ' . (int)$limit;

		$products = ActiveRecord::getDataBySql($sql);

		$ids = array();
		$cnt = array();
		foreach ($products as $prod)
		{
			$ids[] = $prod['ID'];
			$cnt[$prod['ID']] = $prod['cnt'];
		}

		$products = array();
		if ($ids)
		{
			$products = ActiveRecord::getRecordSetArray('Product', new ARSelectFilter(new INCond(new ARFieldHandle('Product', 'ID'), $ids)), array('DefaultImage' => 'ProductImage'));
			foreach ($products as &$prod)
			{
				$prod['count'] = $cnt[$prod['ID']];
			}
			usort($products, array($this, 'togetherStatsSort'));

			ProductPrice::loadPricesForRecordSetArray($products);
		}

		return $products;
	}

	private function togetherStatsSort($a, $b)
	{
		if ($a['count'] == $b['count'])
		{
			return 0;
		}

		return ($a['count'] > $b['count']) ? -1 : 1;
	}

	public function getBundledProducts()
	{
		if (is_null($this->bundledProducts))
		{
			ClassLoader::import('application.model.product.ProductBundle');
			$this->bundledProducts = ProductBundle::getBundledProductSet($this);
		}

		return $this->bundledProducts;
	}

	public function createVariation($variationArray)
	{
		ClassLoader::import('application.model.product.ProductVariationValue');

		$child = $this->createChildProduct();
		$child->save();

		foreach ($variationArray as $variation)
		{
			ProductVariationValue::getNewInstance($child, $variation)->save();
		}

		return $child;
	}

	public function getVariationMatrix()
	{
		$matrix = array();
		$id = $this->getParent()->getID();
		foreach ($this->getParent()->initSet()->getVariationMatrix() as $type => $values)
		{
			if (isset($values[$id]))
			{
				$matrix[$type] = $values[$id];
			}
		}

		return $matrix;
	}

	public function getVariationData(LiveCart $app)
	{
		$id = $this->getParent()->getID();
		$matrix = array();
		foreach ($this->getParent()->initSet()->getVariationData($app) as $type => $values)
		{
			if (isset($values[$id]))
			{
				$matrix[$type] = $values[$id];
			}
		}

		return $matrix;
	}

	public function serialize()
	{
		return parent::serialize(array('categoryID', 'Category', 'manufacturerID', 'defaultImageID'));
	}

	public function __clone()
	{
		parent::__clone();

		$original = $this->originalRecord;
		$original->loadSpecification();
		$this->specificationInstance = clone $original->getSpecification();
		$this->specificationInstance->setOwner($this);

		$this->loadPricing();
		$this->pricingHandlerInstance = clone $this->pricingHandlerInstance;
		$this->pricingHandlerInstance->setProduct($this);

		// images
		if ($this->defaultImage->get())
		{
			$this->defaultImage->set(clone $this->defaultImage->get());
		}

		$this->save();

		// options
		foreach (ProductOption::getProductOptions($this->originalRecord) as $option)
		{
			$clonedOpt = clone $option;
			$clonedOpt->product->set($this);
			$clonedOpt->save();
		}

		// related products
		$groups[] = array();
		foreach ($this->originalRecord->getRelationships() as $relationship)
		{
			$group = $relationship->productRelationshipGroup->get();
			$id = $group ? $group->getID() : null;
			if ($id)
			{
				$groups[$id] = clone $group;
				$groups[$id]->product->set($this);
				$groups[$id]->save();
			}

			$cloned = ProductRelationship::getNewInstance($this, $relationship->relatedProduct->get(), $id ? $groups[$id] : null);
			$cloned->save();
		}
	}

	public function __destruct()
	{
		unset($this->specificationInstance);
		unset($this->pricingHandlerInstance);

		parent::destruct(array('defaultImageID', 'parentID'));
	}
}

?>