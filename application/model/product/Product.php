<?php

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

	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("Product");

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("categoryID", "Category", "ID", null, ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("manufacturerID", "Manufacturer", "ID", null, ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("defaultImageID", "ProductImage", "ID", null, ARInteger::instance()));

		$schema->registerField(new ARField("isEnabled", ARBool::instance()));
		$schema->registerField(new ARField("sku", ARVarchar::instance(20)));
		$schema->registerField(new ARField("name", ARArray::instance()));
		$schema->registerField(new ARField("shortDescription", ARArray::instance()));
		$schema->registerField(new ARField("longDescription", ARArray::instance()));
		$schema->registerField(new ARField("keywords", ARText::instance()));

		$schema->registerField(new ARField("dateCreated", ARDateTime::instance()));
		$schema->registerField(new ARField("dateUpdated", ARDateTime::instance()));

		$schema->registerField(new ARField("URL", ARVarchar::instance(256)));
		$schema->registerField(new ARField("isFeatured", ARBool::instance()));
		$schema->registerField(new ARField("type", ARInteger::instance(4)));

		$schema->registerField(new ArField("voteSum", ARInteger::instance()));
		$schema->registerField(new ArField("voteCount", ARInteger::instance()));
		$schema->registerField(new ArField("rating", ARFloat::instance(8)));

		$schema->registerField(new ArField("minimumQuantity", ARFloat::instance(8)));
		$schema->registerField(new ArField("shippingSurchargeAmount", ARFloat::instance(8)));
		$schema->registerField(new ArField("isSeparateShipment", ARBool::instance()));
		$schema->registerField(new ArField("isFreeShipping", ARBool::instance()));
		$schema->registerField(new ArField("isBackOrderable", ARBool::instance()));
		$schema->registerField(new ArField("isFractionalUnit", ARBool::instance()));

		$schema->registerField(new ArField("shippingWeight", ARFloat::instance(8)));

		$schema->registerField(new ArField("stockCount", ARFloat::instance(8)));
		$schema->registerField(new ArField("reservedCount", ARFloat::instance(8)));
		$schema->registerField(new ArField("salesRank", ARInteger::instance()));
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

	public function isRelatedTo(Product $product)
	{
		return ProductRelationship::hasRelationship($product, $this);
	}

	/**
	 *  Check if the product is available for purchasing
	 */
	public function isAvailable()
	{
		if (!$this->isLoaded())
		{
			$this->load();
		}

		return self::isAvailableForOrdering($this->isEnabled->get(), $this->stockCount->get(), $this->isBackOrderable->get(), $this->type->get());
	}

	/**
	 *  Determines if the product is downloadable (digital file) - as opposed to shippable products
	 */
	public function isDownloadable()
	{
		return $this->type->get() == self::TYPE_DOWNLOADABLE;
	}

	protected static function isAvailableForOrdering($isEnabled, $stockCount, $isBackOrderable, $type)
	{
		if ($isEnabled)
		{
			$config = self::getApplication()->getConfig();

			if (($config->get('INVENTORY_TRACKING') == 'DISABLE') || $type == Product::TYPE_DOWNLOADABLE)
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

		// set prices
		$this->loadPricingFromRequest($request);
		$this->loadPricingFromRequest($request, true);

		// set SpecField's
		$fields = $this->category->get()->getSpecificationFieldSet(Category::INCLUDE_PARENT);
		foreach ($fields as $field)
		{
			$fieldName = $field->getFormFieldName();

			if ($field->isSelector())
			{
				if (!$field->isMultiValue->get())
				{
					if ($request->isValueSet($fieldName) && !in_array($request->get($fieldName), array('other', '')))
				  	{
				  		$this->setAttributeValue($field, SpecFieldValue::getInstanceByID((int)$request->get($fieldName), ActiveRecordModel::LOAD_DATA));
				  	}
				}
				else
				{
					$values = $field->getValuesSet();

					foreach ($values as $value)
					{
					  	if ($request->isValueSet($value->getFormFieldName()) || $request->isValueSet('checkbox_' . $value->getFormFieldName()))
					  	{
						  	if ($request->get($value->getFormFieldName()))
						  	{
								$this->setAttributeValue($field, $value);
							}
							else
							{
								$this->removeAttributeValue($field, $value);
							}
						}
					}
				}
			}
			else
			{
				if ($request->isValueSet($fieldName))
			  	{
			  		if ($field->isTextField())
					{
						$languages = self::getApplication()->getLanguageArray(LiveCart::INCLUDE_DEFAULT);
						foreach ($languages as $language)
						{
						  	if ($request->isValueSet($field->getFormFieldName($language)))
						  	{
								$this->setAttributeValueByLang($field, $language, $request->get($field->getFormFieldName($language)));
							}
						}
					}
					else
					{
						if (strlen($request->get($fieldName)))
						{
							$this->setAttributeValue($field, $request->get($fieldName));
						}
						else
						{
							$this->removeAttribute($field);
						}
					}
				}
			}
		}
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

	public function getPrice($currencyCode, $recalculate = true)
	{
	  	if ($currencyCode instanceof Currency)
	  	{
			$currencyCode = $currencyCode->getID();
		}

		$instance = $this->getPricingHandler()->getPriceByCurrencyCode($currencyCode);
	  	if (!$instance->price->get() && $recalculate)
	  	{
	  		return $instance->reCalculatePrice();
		}
		else
		{
			return $instance->price->get();
		}
	}

	public function addRelatedProduct(Product $product)
	{
		$relationship = ProductRelationship::getNewInstance($this, $product);
		$this->getRelationships()->add($relationship);
		$this->getRemovedRelationships()->removeRecord($relationship);
	}

	public function removeFromRelatedProducts(Product $product)
	{
		$this->getRelationships();
		$relationship = ProductRelationship::getInstance($this, $product);

		$this->relationships->removeRecord($relationship);

		$this->getRemovedRelationships()->add($relationship);
	}

	public function markAsNotLoaded()
	{
		parent::markAsNotLoaded();
		$this->relationships = null;
	}

	/*####################  Saving ####################*/

	/**
	 * Inserts new product record to a database
	 *
	 */
	protected function insert()
	{
		ActiveRecordModel::beginTransaction();

		try
		{
			parent::insert();

			// update category product count numbers
			$catUpdate = new ARUpdateFilter();

			$catUpdate->addModifier('totalProductCount', new ARExpressionHandle('totalProductCount + 1'));

			if ($this->isEnabled->get())
			{
				$catUpdate->addModifier('activeProductCount', new ARExpressionHandle('activeProductCount + 1'));

				if ($this->stockCount->get() > 0)
				{
					$catUpdate->addModifier('availableProductCount', new ARExpressionHandle('availableProductCount + 1'));
				}
			}

			$this->updateCategoryCounters($catUpdate);

			$update = new ARUpdateFilter();
			$update->addModifier('dateUpdated', new ARExpressionHandle('NOW()'));
			$update->addModifier('dateCreated', new ARExpressionHandle('NOW()'));
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
	 * Updates product record
	 *
	 */
	protected function update()
	{
		ActiveRecordModel::beginTransaction();

		try
		{
			parent::update();

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

			$this->updateCategoryCounters($catUpdate);

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

		parent::save($forceOperation);

		$this->getSpecification()->save();
		$this->getPricingHandler()->save();
		$this->saveRelationships();

		// generate SKU automatically if not set
		if (!$this->sku->get())
		{
			ClassLoader::import('application.helper.check.IsUniqueSkuCheck');

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

			$this->sku->set('SKU' . $sku);
			$this->save();
		}

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

			// modify product counters for categories
			$catUpdate = new ARUpdateFilter();

			$catUpdate->addModifier('totalProductCount', new ARExpressionHandle('totalProductCount - 1'));

			if ($product->isEnabled->get())
			{
				$catUpdate->addModifier('activeProductCount', new ARExpressionHandle('activeProductCount - 1'));

				if ($product->stockCount->get() > 0)
				{
					$catUpdate->addModifier('availableProductCount', new ARExpressionHandle('availableProductCount -1'));
				}
			}

			$product->updateCategoryCounters($catUpdate);

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

	protected function updateCategoryCounters(ARUpdateFilter $catUpdate)
	{
		if ($catUpdate->isModifierSet())
		{
			$categoryPathNodes = $this->category->get()->getPathNodeArray(Category::INCLUDE_ROOT_NODE);
			$catIDs = array();
			foreach ($categoryPathNodes as $node)
			{
				$catIDs[] = $node['ID'];
			}
			$catIDs[] = $this->category->get()->getID();

			$catUpdate->setCondition(new INCond(new ARFieldHandle('Category', 'ID'), $catIDs));

			ActiveRecordModel::updateRecordSet('Category', $catUpdate);
		}
	}

	public function saveRelationships()
	{
		if (is_null($this->relationships))
		{
			return;
		}

		foreach($this->getRelationships() as $relationship)
		{
			$relationship->save();
		}

		foreach($this->getRemovedRelationships() as $relationship)
		{
			$relationship->delete();
		}
	}

	/*####################  Data array transformation ####################*/

	public static function sortAttributesByHandle(&$array)
	{
		if (isset($array['attributes']))
		{
			foreach ($array['attributes'] as $attr)
			{
				if (isset($attr['SpecField']))
				{
					$array['byHandle'][$attr['SpecField']['handle']] = $attr;
				}
				else
				{
					if (!$attr['isMultiValue'])
					{
						$array['byHandle'][$attr['handle']] = $attr;
					}
					else
					{
						$array['byHandle'][$attr['handle']][$attr['specFieldValueID']] = $attr;
					}
				}
			}
		}
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

		$this->setArrayData($array);

	  	return $array;
	}

	public static function transformArray($array, ARSchema $schema)
	{
		$array = parent::transformArray($array, $schema);

		$array['isTangible'] = $array['type'] == self::TYPE_TANGIBLE;
		$array['isDownloadable'] = $array['type'] == self::TYPE_DOWNLOADABLE;

		if ($array['isEnabled'])
		{
			if ($array['isDownloadable'])
			{
				$array['isAvailable'] = true;
			}
			else
			{
				$array['isAvailable'] = self::isAvailableForOrdering($array['isEnabled'], $array['stockCount'], $array['isBackOrderable'], $array['type']);
			}
		}
		else
		{
			$array['isAvailable'] = false;
		}

		return $array;
	}

	/*####################  Get related objects ####################*/

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

	private function loadRelationships($loadReferencedRecords)
	{
		ClassLoader::import('application.model.product.ProductRelationship');
		$this->relationships = ProductRelationship::getRelationships($this, $loadReferencedRecords);
	}

	/**
	 * @return ARSet
	 */
	public function getRelationships($loadReferencedRecords = array('RelatedProduct' => 'Product', 'DefaultImage' => 'ProductImage', 'Manufacturer', 'ProductRelationshipGroup'))
	{
		if(is_null($this->relationships))
		{
			$this->loadRelationships($loadReferencedRecords);
		}

		return $this->relationships;
	}

	/**
	 * @return ARSet
	 */
	public function getRelationshipsArray($loadReferencedRecords = array('RelatedProduct' => 'Product', 'DefaultImage' => 'ProductImage', 'Manufacturer', 'ProductRelationshipGroup'))
	{
		ClassLoader::import('application.model.product.ProductRelationship');
		return ProductRelationship::getRelationshipsArray($this, $loadReferencedRecords);
	}

	/**
	 * @return ARSet
	 */
	public function getRelatedProducts()
	{
		$relatedProducts = new ARSet();

		foreach($this->getRelationships() as $relationship)
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
	public function getRelationshipGroups()
	{
		ClassLoader::import('application.model.product.ProductRelationshipGroup');
		return ProductRelationshipGroup::getProductGroups($this);
	}

	/**
	 * @return array
	 */
	public function getRelationshipGroupArray()
	{
		ClassLoader::import('application.model.product.ProductRelationshipGroup');
		return ProductRelationshipGroup::getProductGroupArray($this);
	}

	public function getRelatedProductsWithGroupsArray()
	{
		ClassLoader::import('application.model.product.ProductRelationshipGroup');
		return ProductRelationshipGroup::mergeGroupsWithFields($this->getRelationshipGroupArray(), $this->getRelationshipsArray());
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
		ClassLoader::import('application.model.product.ProductOption');
		$f = new ARSelectFilter();
		$f->setOrder(new ARFieldHandle('ProductOption', 'position'), 'ASC');
		$options = $this->getRelatedRecordSet('ProductOption', $f, array('DefaultChoice' => 'ProductOptionChoice'));

		if ($includeInheritedOptions)
		{
			$options->merge($this->category->get()->getOptions(true));

			ProductOption::loadChoicesForRecordSet($options);
		}

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

	public function serialize()
	{
		return parent::serialize(array('categoryID', 'Category', 'manufacturerID', 'defaultImageID'));
	}

	public function __destruct()
	{
		unset($this->specificationInstance);
		unset($this->pricingHandlerInstance);

		parent::destruct(array('defaultImageID'));
	}
}

?>