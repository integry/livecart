<?php

ClassLoader::import("application.model.eavcommon.iEavFieldManager");
ClassLoader::import("application.model.eav.EavAble");
ClassLoader::import("application.model.eav.EavObject");
ClassLoader::import("application.model.system.ActiveTreeNode");
ClassLoader::import("application.model.system.MultilingualObject");
ClassLoader::import("application.model.category.CategoryImage");
ClassLoader::import("application.model.filter.*");

/**
 * Hierarchial product category structure.
 *
 * Each product belongs to one particular category. The category structure has a root node (ID = 1).
 * The category tree is based on a modified preordered tree traversal model (http://www.sitepoint.com/article/hierarchical-data-database/2)
 *
 * @package application.model.category
 * @author Integry Systems
 * @todo Update product counts when category is moved
 */
class Category extends ActiveTreeNode implements MultilingualObjectInterface, iEavFieldManager, EavAble
{
	const INCLUDE_PARENT = true;

	private $specFieldArrayCache = array();
	private $filterGroupArrayCache = array();
	private $filterSetCache;
	private $subCategorySetCache;

	/**
	 * Define database schema for Category model
	 *
	 * @param string $className Schema name
	 */
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("Category");

		parent::defineSchema($className);

		$schema->registerField(new ARForeignKeyField("defaultImageID", "categoryImage", "ID", 'CategoryImage', ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("eavObjectID", "eavObject", "ID", 'EavObject', ARInteger::instance()), false);
		$schema->registerField(new ARField("name", ARArray::instance()));
		$schema->registerField(new ARField("description", ARArray::instance()));
		$schema->registerField(new ARField("keywords", ARArray::instance()));
		$schema->registerField(new ARField("isEnabled", ARBool::instance()));
		$schema->registerField(new ARField("availableProductCount", ARInteger::instance()));
		$schema->registerField(new ARField("activeProductCount", ARInteger::instance()));
		$schema->registerField(new ARField("totalProductCount", ARInteger::instance()));
	}

	/*####################  Static method implementations ####################*/

	/**
	 * Get catalog item instance
	 *
	 * @param int|array $recordID Record id
	 * @param bool $loadRecordData If true loads record's structure and data
	 * @param bool $loadReferencedRecords If true loads all referenced records
	 * @return Category
	 */
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false)
	{
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords);
	}

	/**
	 * Loads a set of Category active records
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

	/**
	 * Get new Category active record instance
	 *
	 * @param ActiveTreeNode $parent
	 * @return Category
	 */
	public static function getNewInstance(Category $parent)
	{
		$category = parent::getNewInstance(__CLASS__, $parent);

		$category->activeProductCount->set(0);
		$category->availableProductCount->set(0);
		$category->totalProductCount->set(0);

		return $category;
	}

	/*####################  Value retrieval and manipulation ####################*/

	public function getProductCountField()
	{
		$config = self::getApplication()->getConfig();
		return ($config->get('INVENTORY_TRACKING') != 'ENABLE_AND_HIDE') ? 'activeProductCount' :'availableProductCount';
	}

	public function setValueByLang($fieldName, $langCode, $value)
	{
		return MultiLingualObject::setValueByLang($fieldName, $langCode, $value);
	}

	public function getValueByLang($fieldName, $langCode = null, $returnDefaultIfEmpty = true)
	{
		return MultiLingualObject::getValueByLang($fieldName, $langCode, $returnDefaultIfEmpty);
	}

	public function setValueArrayByLang($fieldNameArray, $defaultLangCode, $langCodeArray, Request $request)
	{
		return MultiLingualObject::setValueArrayByLang($fieldNameArray, $defaultLangCode, $langCodeArray, $request);
	}

	public function isEnabled()
	{
		$this->load();
		return $this->isEnabled->get();
	}

	public function getActiveProductCount()
	{
		$field = $this->getProductCountField();

		return $this->$field->get();
	}

	public function getProductCount(ProductFilter $productFilter)
	{
		$query = new ARSelectQueryBuilder();
		$query->includeTable('Product');
		$query->addField('COUNT(*) AS cnt');
		$filter = $this->getProductsFilter($productFilter);
		$filter->setLimit(0);
		$query->setFilter($filter);
		$data = ActiveRecord::getDataBySQL($query->getPreparedStatement(ActiveRecord::getDBConnection()));
		return $data[0]['cnt'];
	}

	/**
	 * Gets a subcategory count
	 *
	 * @return int
	 */
	public function getSubcategoryCount()
	{
		$this->load();
		$productCount = ($this->rgt->get() - $this->lft->get() - 1) / 2;
		return $productCount;
	}

	public function moveTo(Category $parentNode, Category $beforeNode = null)
	{
		self::beginTransaction();
		$result = parent::moveTo($parentNode, $beforeNode);
		self::recalculateProductsCount();
		self::commit();

		return $result;
	}

	public function isRoot()
	{
		return self::ROOT_ID == $this->getID();
	}

	/*####################  Saving ####################*/

	/**
	 *
	 * @todo fix potential bug: when using $this->load() in method, it might
	 * overwrite the data that was set during runtime
	 */
	protected function update()
	{
		ActiveRecordModel::beginTransaction();
		try
		{
			parent::update();
			$activeProductCount = $this->activeProductCount->get();
			if ($this->isEnabled->isModified())
			{
				if ($this->isEnabled())
				{
					$activeProductCountUpdateStr = "activeProductCount + " . $activeProductCount;
				}
				else
				{
					$activeProductCountUpdateStr = "activeProductCount - " . $activeProductCount;
				}
				$pathNodes = $this->getPathNodeSet(true);

				foreach ($pathNodes as $node)
				{
					if ($node->getID() != $this->getID())
					{
						$f = new ARUpdateFilter();
						$f->addModifier('activeProductCount', new ARExpressionHandle($activeProductCountUpdateStr));
						$node->updateRecord($f);
					}
				}
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
	 * Removes category by ID and fixes data in parent categories
	 * (updates activeProductCount and totalProductCount)
	 *
	 * @param int $recordID
	 */
	public function delete()
	{
		ActiveRecordModel::beginTransaction();

		try
		{
			$activeProductCount = $this->activeProductCount->get();
			$totalProductCount = $this->totalProductCount->get();
			$availableProductCount = $this->availableProductCount->get();

			foreach ($this->getPathNodeSet(true) as $node)
			{
				$node->setFieldValue("activeProductCount", "activeProductCount - " . $activeProductCount);
				$node->setFieldValue("totalProductCount", "totalProductCount - " . $totalProductCount);
				$node->setFieldValue("availableProductCount", "availableProductCount - " . $availableProductCount);

				$node->save();
			}
			ActiveRecordModel::commit();

			parent::delete();
		}
		catch (Exception $e)
		{
			ActiveRecordModel::rollback();
			throw $e;
		}
	}

	/*####################  Data array transformation ####################*/

	/**
	 * Creates array representation
	 *
	 * @return array
	 */
	protected static function transformArray($array, ARSchema $schema)
	{
		$array = MultiLingualObject::transformArray($array, $schema);
		$array['unavailableProductCount'] = $array['totalProductCount'] - $array['availableProductCount'];
		$array['inactiveProductCount'] = $array['totalProductCount'] - $array['activeProductCount'];
		$c = self::getApplication()->getConfig();

		$array['count'] = ('ENABLE_AND_HIDE' == $c->get('INVENTORY_TRACKING')) ? $array['availableProductCount'] : $array['activeProductCount'];
		return $array;
	}

	/*####################  Get related objects ####################*/

	/**
	 * Returns a set of direct subcategories
	 *
	 * @param bool $loadReferencedRecords
	 * @return ARSet
	 */
	public function getSubcategorySet($loadReferencedRecords = false)
	{
	  	if (!$this->subCategorySetCache)
	  	{
			$this->subCategorySetCache = ActiveRecord::getRecordSet('Category', $this->getSubcategoryFilter(), $loadReferencedRecords);
		}

		return $this->subCategorySetCache;
	}

	/**
	 * Returns an array of direct subcategories
	 *
	 * @param bool $loadReferencedRecords
	 * @return ARSet
	 */
	public function getSubcategoryArray($loadReferencedRecords = false)
	{
	  	return ActiveRecord::getRecordSetArray('Category', $this->getSubcategoryFilter(), $loadReferencedRecords);
	}

	public function getSubcategoryFilter($returnEmpty = false)
	{
	  	$filter = new ARSelectFilter();
	  	$cond = new EqualsCond(new ARFieldHandle('Category', 'parentNodeID'), $this->getID());
	  	$cond->addAND(new EqualsCond(new ARFieldHandle('Category', 'isEnabled'), 1));

		// Hide empty categories
		if (!$returnEmpty)
		{
			$config = self::getApplication()->getConfig();
			if ('ENABLE_AND_HIDE' == $config->get('INVENTORY_TRACKING'))
			{
				$cond->addAND(new MoreThanCond(new ARFieldHandle('Category', $this->getProductCountField()), 0));
			}
		}

		$filter->setCondition($cond);
	  	$filter->setOrder(new ARFieldHandle('Category', 'lft'), 'ASC');

	  	return $filter;
	}

	/**
	 * Returns a set of siblings (categories with the same parent)
	 *
	 * @param bool $loadSelf whether to include own instance
	 * @param bool $loadReferencedRecords
	 * @return ARSet
	 */
	public function getSiblingSet($loadSelf = true, $loadReferencedRecords = false)
	{
	  	return ActiveRecord::getRecordSet('Category', $this->getSiblingFilter($loadSelf), $loadReferencedRecords);
	}

	/**
	 * Returns an array of siblings (categories with the same parent)
	 *
	 * @param bool $loadSelf whether to include own instance
	 * @param bool $loadReferencedRecords
	 * @return ARSet
	 */
	public function getSiblingArray($loadSelf = true, $loadReferencedRecords = false)
	{
	  	return ActiveRecord::getRecordSetArray('Category', $this->getSiblingFilter($loadSelf), $loadReferencedRecords);
	}

	/**
	 *
	 *
	 * @param bool $loadSelf
	 * @return ARSelectFilter
	 */
	private function getSiblingFilter($loadSelf)
	{
	  	$filter = new ARSelectFilter();
	  	$cond = new EqualsCond(new ARFieldHandle('Category', 'parentNodeID'), $this->parentNode->get()->getID());
	  	$cond->addAND(new EqualsCond(new ARFieldHandle('Category', 'isEnabled'), 1));

		if (!$loadSelf)
		{
			$cond->addAND(new NotEqualsCond(new ARFieldHandle('Category', 'ID'), $this->getID()));
		}

		$filter->setCondition($cond);
	  	$filter->setOrder(new ARFieldHandle('Category', 'lft'), 'ASC');

	  	return $filter;
	}

	/**
	 * @return ActiveTreeNode
	 */
	public static function getRootNode()
	{
		$node = parent::getRootNode(__CLASS__);
		$node->load();
		return $node;
	}

	public function getBranchFilter(ARSelectFilter $filter = null)
	{
		if (is_null($filter))
		{
			$filter = new ARSelectFilter();
		}

		$filter->setOrder(new ARFieldHandle("Category", "lft"), 'ASC');
		$filter->mergeCondition(new MoreThanCond(new ARFieldHandle("Category", "lft"), $this->lft->get()));
		$filter->mergeCondition(new LessThanCond(new ARFieldHandle("Category", "rgt"), $this->rgt->get()));

		return $filter;
	}

	public function getPathNodeArray($includeRootNode = false, $loadReferencedRecords = false)
	{
		if (is_null($this->pathNodeArray))
		{
			$this->pathNodeArray = parent::getPathNodeArray(true, $loadReferencedRecords);
		}

		$array = $this->pathNodeArray;
		if (!$includeRootNode)
		{
			array_shift($array);
		}

		return $array;
	}

	/**
	 * Gets a list of products assigned to this node
	 *
	 * @param bool $loadReferencedRecords
	 * @return array
	 */
	public function getProductArray(ProductFilter $productFilter, $loadReferencedRecords = false)
	{
		return ActiveRecordModel::getRecordSetArray('Product', $this->getProductsFilter($productFilter), $loadReferencedRecords);
	}

	/**
	 * Gets a list of products assigned to this node
	 * @param bool $loadReferencedRecords
	 *
	 * @return ARSet
	 */
	public function getProductSet(ArSelectFilter $filter, $loadReferencedRecords = false)
	{
		return $this->getRelatedRecordSet("Product", $this->getProductFilter($filter), $loadReferencedRecords);
	}

	/**
	 *	Create a basic ARSelectFilter object to select category products
	 */
	public function getProductsFilter(ProductFilter $productFilter)
	{
		$filter = $productFilter->getSelectFilter();
		$filter->mergeCondition($this->getProductCondition($productFilter->isSubcategories()));
		$this->applyInventoryFilter($filter);

		return $filter;
	}

	public function getProductCondition($includeSubcategories = false)
	{
		if ($includeSubcategories)
		{
			$cond = new EqualsOrMoreCond(new ARFieldHandle('Category', 'lft'), $this->lft->get());
			$cond->addAND(new EqualsOrLessCond(new ARFieldHandle('Category', 'rgt'), $this->rgt->get()));
			$cond->addOr(new INCond(new ARFieldHandle('Product', 'ID'), 'SELECT ProductCategory.productID FROM ProductCategory LEFT JOIN Category ON ProductCategory.categoryID=Category.ID WHERE Category.lft>=' . $this->lft->get() . ' AND Category.rgt<=' . $this->rgt->get()));
		}
		else
		{
			$cond = new EqualsCond(new ARFieldHandle('Product', 'categoryID'), $this->getID());
			$cond->addOr(new INCond(new ARFieldHandle('Product', 'ID'), 'SELECT ProductCategory.productID FROM ProductCategory WHERE ProductCategory.categoryID=' . $this->getID()));
		}

		return $cond;
	}

	public function getProductFilter(ARSelectFilter $filter)
	{
		$filter->mergeCondition(new EqualsCond(new ARFieldHandle('Product', 'isEnabled'), 1));

		$this->applyInventoryFilter($filter);

		return $filter;
	}

	private function applyInventoryFilter(ARSelectFilter $filter)
	{
		$c = self::getApplication()->getConfig();
		if ($c->get('INVENTORY_TRACKING') == 'ENABLE_AND_HIDE')
		{
			$cond = new MoreThanCond(new ARFieldHandle('Product', 'stockCount'), 0);
			$cond->addOr(new EqualsCond(new ARFieldHandle('Product', 'isBackOrderable'), 1));
			$filter->mergeCondition($cond);
		}
	}

	public function getFilterSet()
	{
		if ($this->filterSetCache)
		{
			return $this->filterSetCache;
		}

		Classloader::import('application.model.filter.Filter');
		Classloader::import('application.model.filter.SelectorFilter');

		// get filter groups
	  	$groups = $this->getFilterGroupArray();

		$ids = array();
		$specFields = array();
		foreach ($groups as $group)
		{
			if (in_array($group['SpecField']['type'],
			  			 array(SpecField::TYPE_NUMBERS_SELECTOR, SpecField::TYPE_TEXT_SELECTOR)))
		  	{
				$specFields[] = $group['SpecField']['ID'];
			}
			else
			{
			  	$ids[] = $group['ID'];
			}
		}

		$ret = array();

		if ($ids)
		{
			// get specification simple value filters
			$filterCond = new INCond(new ARFieldHandle('Filter', 'filterGroupID'), $ids);
			$filterFilter = new ARSelectFilter();
			$filterFilter->setCondition($filterCond);
			$filterFilter->setOrder(new ARFieldHandle('Filter', 'filterGroupID'));
			$filterFilter->setOrder(new ARFieldHandle('Filter', 'position'));

			$valueFilters = ActiveRecord::getRecordSet('Filter', $filterFilter, array('FilterGroup', 'SpecField'));
			foreach ($valueFilters as $filter)
			{
				$ret[] = $filter;
			}
		}

		// get specification selector value filters
		if ($specFields)
		{
			$selectFilter = new ARSelectFilter();
			$selectFilter->setCondition(new INCond(new ARFieldHandle('SpecFieldValue', 'specFieldID'), $specFields));
			$selectFilter->setOrder(new ARFieldHandle('SpecFieldValue', 'specFieldID'));
			$selectFilter->setOrder(new ARFieldHandle('SpecFieldValue', 'position'));

			$specFieldValues = ActiveRecord::getRecordSet('SpecFieldValue', $selectFilter);
			foreach ($specFieldValues as $value)
			{
				$ret[] = new SelectorFilter($value);
			}
		}

		$this->filterSetCache = $ret;

		return $ret;
	}

	/**
	 * Returns a set of category filters
	 *
	 * @param bool $loadReferencedRecords
	 * @return ARSet
	 */
	public function getFilterGroupSet($includeParentFields = true)
	{
	  	ClassLoader::import('application.model.filter.FilterGroup');
		$filter = $this->getFilterGroupFilter($includeParentFields);
		if (!$filter)
		{
		  	return new ARSet(null);
		}
		return ActiveRecord::getRecordSet('FilterGroup', $filter, array('SpecField'));
	}

	/**
	 * Returns a set of category filters
	 *
	 * @param bool $loadReferencedRecords
	 * @return ARSet
	 */
	public function getFilterGroupArray()
	{
		if (!$this->filterGroupArrayCache)
		{
		  	ClassLoader::import('application.model.filter.FilterGroup');
			$filter = $this->getFilterGroupFilter();
			if (!$filter)
			{
			  	return array();
			}

			$this->filterGroupArrayCache = ActiveRecord::getRecordSetArray('FilterGroup', $filter, array('SpecField'));
		}

		return $this->filterGroupArrayCache;
	}

	private function getFilterGroupFilter($includeParentFields = true)
	{
		if (!$this->specFieldArrayCache)
		{
			$this->specFieldArrayCache = $this->getSpecificationFieldArray(true);
		}

		$categories = array();
		$ids = array();
		foreach ($this->specFieldArrayCache as $field)
		{
		  	if(!empty($field['ID'])) $ids[$field['ID']] = true;
		}

		if (!$ids)
		{
		  	return false;
		}

		$ids = array_keys($ids);

		$filter = new ARSelectFilter();
		$filter->setOrder(new ARFieldHandle("SpecField", "categoryID"));
		$filter->setOrder(new ARFieldHandle("FilterGroup", "position"));

		$cond = new INCond(new ARFieldHandle("FilterGroup", "specFieldID"), $ids);

		$filter->setCondition($cond);

		return $filter;
	}

	/**
	 * Returns a set of category images
	 *
	 * @return ARSet
	 */
	public function getCategoryImagesSet()
	{
	  	ClassLoader::import('application.model.category.CategoryImage');

		return ActiveRecord::getRecordSet('CategoryImage', $this->getCategoryImagesFilter());
	}

	private function getCategoryImagesFilter()
	{
		$filter = new ARSelectFilter();
		$filter->setCondition(new EqualsCond(new ARFieldHandle('CategoryImage', 'categoryID'), $this->getID()));
		$filter->setOrder(new ARFieldHandle('CategoryImage', 'position'), 'ASC');

		return $filter;
	}

 	/**
	 * Creates an array from active record instances of SpecFieldGroup by using a filter
	 *
	 * @param bool $loadReferencedRecords
	 *
	 * @return ARSet
	 */
	public function getSpecificationFieldGroupArray($loadReferencedRecords = false)
	{
		ClassLoader::import("application.model.category.SpecFieldGroup");
		return ActiveRecordModel::getRecordSetArray('SpecFieldGroup', $this->getSpecificationGroupFilter(), $loadReferencedRecords);
	}

 	/**
	 * Loads a set of active record instances of SpecFieldGroup by using a filter
	 *
	 * @param bool $loadReferencedRecords
	 *
	 * @return ARSet
	 */
	public function getSpecificationFieldGroupSet($loadReferencedRecords = false)
	{
		ClassLoader::import("application.model.category.SpecFieldGroup");

		return ActiveRecordModel::getRecordSet('SpecFieldGroup', $this->getSpecificationGroupFilter(), $loadReferencedRecords);
	}

	/**
	 * Loads a set of spec field records for a category.
	 *
	 * Result includes a list of specification fields from upper category branches
	 * (specification field inheritance)
	 * The result is ordered by a category ID (upper level fields go first and
	 * then by level position)
	 *
	 * @return ARSet
	 */
	public function getSpecificationFieldSet($includeParentFields = false, $loadReferencedRecords = false)
	{
		ClassLoader::import("application.model.category.SpecField");
		return ActiveRecordModel::getRecordSet('SpecField', $this->getSpecificationFilter($includeParentFields), true);
	}

	public function getSpecificationFieldArray($includeParentFields = true, $loadReferencedRecords = false)
	{
		ClassLoader::import("application.model.category.SpecField");
		return ActiveRecordModel::getRecordSetArray('SpecField', $this->getSpecificationFilter($includeParentFields), array('SpecFieldGroup'));
	}

	public function getSpecFieldsWithGroupsArray()
	{
		return ActiveRecordGroup::mergeGroupsWithFields('SpecFieldGroup', $this->getSpecificationFieldGroupArray(), $this->getSpecificationFieldArray(false, true));
	}

	public function getOptions($includeInheritedOptions = false)
	{
		ClassLoader::import('application.model.product.ProductOption');
		$f = new ARSelectFilter();

		if ($includeInheritedOptions)
		{
			$ids = array();
			foreach(array_reverse($this->getPathNodeArray(true)) as $cat)
			{
				$ids[] = $cat['ID'];
				$f->setOrder(new ARExpressionHandle('ProductOption.categoryID=' . $cat['ID']), 'DESC');
			}

			$f->setCondition(new INCond(new ARFieldHandle('ProductOption', 'categoryID'), $ids));
		}
		else
		{
			$f->setCondition(new EqualsCond(new ARFieldHandle('ProductOption', 'categoryID'), $this->getID()));
		}

		$f->setOrder(new ARFieldHandle('ProductOption', 'position'), 'ASC');

		return ProductOption::getRecordSet($f, array('ProductOptionChoice'));
	}

	/**
	 * Crates a select filter for specification fields related to category
	 *
	 * @param bool $includeParentFields
	 * @return ARSelectFilter
	 * @todo Order by categories (top-level category fields go first)
	 */
	private function getSpecificationFilter($includeParentFields)
	{
		$filter = new ARSelectFilter();

		$filter->setOrder(new ARFieldHandle("SpecFieldGroup", "position"), 'ASC');
		$filter->setOrder(new ARFieldHandle("SpecField", "position"), 'ASC');

		$cond = new EqualsCond(new ARFieldHandle("SpecField", "categoryID"), $this->getID());

		if ($includeParentFields)
		{
			foreach (self::getPathNodeArray(Category::INCLUDE_ROOT_NODE) as $node)
			{
				$cond->addOR(new EqualsCond(new ARFieldHandle("SpecField", "categoryID"), $node['ID']));
			}
		}
		$filter->setCondition($cond);

		return $filter;
	}

	/**
	 * Crates a select filter for specification fields groups related to category
	 *$filter->addField('value', $aliasTable, $aliasField);
	 * @return ARSelectFilter
	 */
	private function getSpecificationGroupFilter()
	{
		ClassLoader::import("application.model.category.SpecFieldGroup");

		$filter = new ARSelectFilter();
		$filter->setOrder(new ARFieldHandle("SpecFieldGroup", "position"), ARSelectFilter::ORDER_ASC);
		$filter->setCondition(new EqualsCond(new ARFieldHandle("SpecFieldGroup", "categoryID"), $this->getID()));

		return $filter;
	}

	public function serialize()
	{
		return parent::serialize(array('defaultImageID', 'parentNodeID'));
	}

	/**
	 * Reindex the category tree
	 */
	public static function reindex()
	{
		parent::reindex(__CLASS__);
	}

	public static function recalculateProductsCount()
	{
		ClassLoader::import("application.model.product.Product");

		self::beginTransaction();

		$fields = array('totalProductCount', 'activeProductCount', 'availableProductCount');

		// reset counts to 0
		$sql = 'UPDATE Category SET ';
		foreach ($fields as $field)
		{
			$sql .= $field . '=0' . ('availableProductCount' != $field ? ',' : '');
		}

		self::getDBConnection()->executeUpdate($sql);

		// category product counts
		$sql = 'UPDATE Category SET totalProductCount = (SELECT COUNT(*) FROM Product WHERE categoryID = Category.ID),
									activeProductCount = (SELECT COUNT(*) FROM Product WHERE categoryID = Category.ID AND Product.isEnabled = 1),
									availableProductCount = (SELECT COUNT(*) FROM Product WHERE categoryID = Category.ID AND Product.isEnabled = 1 AND (stockCount > 0 OR type = ' . Product::TYPE_DOWNLOADABLE .  '))';
		self::getDBConnection()->executeUpdate($sql);

		// additional categories
		$sql = 'UPDATE Category SET totalProductCount = totalProductCount + (SELECT COUNT(*) FROM ProductCategory WHERE ProductCategory.categoryID = Category.ID),
									activeProductCount = activeProductCount + (SELECT COUNT(*) FROM ProductCategory LEFT JOIN Product ON ProductCategory.productID=Product.ID WHERE ProductCategory.categoryID = Category.ID AND Product.isEnabled = 1),
									availableProductCount = availableProductCount + (SELECT COUNT(*) FROM ProductCategory LEFT JOIN Product ON ProductCategory.productID=Product.ID WHERE ProductCategory.categoryID = Category.ID AND Product.isEnabled = 1 AND (stockCount > 0 OR type = ' . Product::TYPE_DOWNLOADABLE .  '))';
		self::getDBConnection()->executeUpdate($sql);

		//self::updateProductCount(Category::getInstanceByID(Category::ROOT_ID, Category::LOAD_DATA));

		// add subcategory counts to parent categories
		// @todo - rewrite so this wouldn't use temporary tables - possible?
		$sql = 'CREATE TEMPORARY TABLE CategoryCount
					SELECT ID';

		foreach ($fields as $field)
		{
			$sql .= ', (SELECT SUM(' . $field . ')
			FROM Category AS cat
			WHERE cat.lft >= Category.lft
				AND cat.rgt <= Category.rgt) AS ' . $field;
		}

		$sql .= ' FROM Category';

		self::getDBConnection()->executeUpdate($sql);

		$sql = 'UPDATE Category LEFT JOIN CategoryCount ON Category.ID=CategoryCount.ID SET ';
		foreach ($fields as $field)
		{
			$sql .= 'Category.' . $field . '=CategoryCount.' . $field . ('availableProductCount' != $field ? ',' : '');
		}

		self::getDBConnection()->executeUpdate($sql);
		self::getDBConnection()->executeUpdate('DROP TEMPORARY TABLE CategoryCount');

		self::commit();
	}

	// subcategory counts
	private static function updateProductCount(Category $category)
	{
		$countTotal = $countAvailable = $countActive = 0;
		foreach ($category->getSubCategorySet() as $sub)
		{
			self::updateProductCount($sub);
			$countTotal += $sub->totalProductCount->get();
			$countAvailable += $sub->availableProductCount->get();
			$countActive += $sub->activeProductCount->get();
		}

		$category->totalProductCount->set($category->totalProductCount->get() + $countTotal);
		$category->activeProductCount->set($category->activeProductCount->get() + $countActive);
		$category->availableProductCount->set($category->availableProductCount->get() + $countAvailable);
		$category->save();
	}

	public function __clone()
	{
		//foreach
	}
}

?>
