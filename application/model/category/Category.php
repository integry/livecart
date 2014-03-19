<?php

namespace category;

use system\ActiveTreeNode;

/**
 * Hierarchial product category structure.
 *
 * Each product belongs to one particular category. The category structure has a root node (ID = 1).
 * The category tree is based on a modified preordered tree traversal model (http://www.sitepoint.com/article/hierarchical-data-database/2)
 *
 * @package application/model/category
 * @author Integry Systems
 * @todo Update product counts when category is moved
 */
class Category extends ActiveTreeNode //implements MultilingualObjectInterface, iEavFieldManager, EavAble
{
	const INCLUDE_PARENT = true;

	private $specFieldArrayCache = array();
	private $filterGroupArrayCache = null;
	private $filterSetCache;
	private $subCategorySetCache;
	private $subCategoryArray = null;

//	public $defaultImageID", "categoryImage", "ID", 'CategoryImage;
//	public $eavObjectID", "eavObject", "ID", 'EavObject', ARInteger::instance()), false);
	public $name;
	public $description;
	public $keywords;
	public $pageTitle;
	public $isEnabled;
	public $availableProductCount;
	public $activeProductCount;
	public $totalProductCount;

	/*####################  Static method implementations ####################*/

	public function initialize()
	{
		$this->belongsTo('parentNodeID', 'category\Category', 'ID', array('alias' => 'Category'));
		
        $this->hasMany('parentNodeID', 'category\Category', 'ID', array(
            'alias' => 'Category',
            'foreignKey' => array(
                'action' => \Phalcon\Mvc\Model\Relation::ACTION_CASCADE
            )
        ));
		
        $this->hasMany('ID', 'product\Product', 'productID', array(
            'alias' => 'Product',
            'foreignKey' => array(
                'action' => \Phalcon\Mvc\Model\Relation::ACTION_CASCADE
            )
        ));
        
        $this->hasMany('ID', 'category\ProductCategory', 'categoryID', array(
            'alias' => 'ProductCategory',
            'foreignKey' => array(
                'action' => \Phalcon\Mvc\Model\Relation::ACTION_CASCADE
            )
        ));

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

		$category->activeProductCount = 0;
		$category->availableProductCount = 0;
		$category->totalProductCount = 0;

		return $category;
	}

	/*####################  Value retrieval and manipulation ####################*/

	public function getProductCountField()
	{
		$config = $this->getConfig();
		return ($config->get('INVENTORY_TRACKING') != 'ENABLE_AND_HIDE') ? 'activeProductCount' :'availableProductCount';
	}

	public function setValueByLang($fieldName, $langCode, $value)
	{
		return MultiLingualObject::setValueByLang($fieldName, $langCode, $value);
	}

	public function isEnabled()
	{
		return $this->isEnabled;
	}

	public function getActiveProductCount()
	{
		$field = $this->getProductCountField();

		return $this->$field;
	}

	public function getProductCount(ProductFilter $productFilter)
	{
		$query = new ARSelectQueryBuilder();
		$query->includeTable('Product');
		$query->addField('COUNT(*) AS cnt');
		$filter = $this->getProductsFilter($productFilter);
		$filter->limit(0);
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
		$productCount = ($this->rgt - $this->lft - 1) / 2;
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
	public function afterUpdate()
	{
		return;
		parent::afterUpdate();
		ActiveRecordModel::beginTransaction();
		try
		{
			$activeProductCount = $this->activeProductCount;
			if ($this->hasChanged('isEnabled'))
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
		parent::delete();
		return;
		ActiveRecordModel::beginTransaction();

		try
		{
			$activeProductCount = $this->activeProductCount;
			$totalProductCount = $this->totalProductCount;
			$availableProductCount = $this->availableProductCount;

			foreach ($this->getPathNodeSet(true) as $node)
			{
				$node->writeAttribute("activeProductCount", "activeProductCount - " . $activeProductCount);
				$node->writeAttribute("totalProductCount", "totalProductCount - " . $totalProductCount);
				$node->writeAttribute("availableProductCount", "availableProductCount - " . $availableProductCount);

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
		$c = $this->getConfig();

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
	  	if (!$this->subCategoryArray || $loadReferencedRecords)
	  	{
	  		$this->subCategoryArray = $this->getSubcategoryFilter()->getQuery()->execute();
		}

		return $this->subCategoryArray;
	}

	public function getSubcategoryFilter($returnEmpty = false)
	{
	  	$filter = $this->getDI()->get('modelsManager')->createBuilder()->from(__CLASS__);
	  	$filter->andWhere('parentNodeID = :parentNodeID: AND isEnabled=1', array('parentNodeID' => $this->getID()));

		// Hide empty categories
		if (!$returnEmpty)
		{
			$config = $this->getConfig();
			if ('ENABLE_AND_HIDE' == $config->get('INVENTORY_TRACKING'))
			{
				$cond->andWhere($this->getProductCountField() . ' > 0');
			}
		}

	  	$filter->orderBy('lft ASC');

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
	/*
	private function getSiblingFilter($loadSelf)
	{
	  	$filter = new ARSelectFilter();
	  	$cond = 'Category.parentNodeID = :Category.parentNodeID:', array('Category.parentNodeID' => $this->parentNode->getID());
	  	$cond->andWhere('Category.isEnabled = :Category.isEnabled:', array('Category.isEnabled' => 1));

		if (!$loadSelf)
		{
			$cond->andWhere(new NotEqualsCond('Category.ID', $this->getID()));
		}

		$filter->setCondition($cond);
	  	$filter->orderBy('Category.lft', 'ASC');

	  	return $filter;
	}

	public function getBranchFilter(ARSelectFilter $filter = null)
	{
		if (is_null($filter))
		{
			$filter = new ARSelectFilter();
		}

		$filter->orderBy(new ARFieldHandle("Category", "lft"), 'ASC');
		$filter->andWhere(new MoreThanCond(new ARFieldHandle("Category", "lft"), $this->lft));
		$filter->andWhere(new LessThanCond(new ARFieldHandle("Category", "rgt"), $this->rgt));

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
	*/

	/**
	 * Gets a list of products assigned to this node
	 *
	 * @param bool $loadReferencedRecords
	 * @return array
	 */
	public function getProductArray(ProductFilter $productFilter, $loadReferencedRecords = false)
	{
		return ActiveRecordModel::getRecordSetArray('Product', $productFilter->getSelectFilter(), $loadReferencedRecords);
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
		$filter = new ARSelectFilter($this->getProductCondition($productFilter->isSubcategories()));
		$filter->andWhere('Product.isEnabled = :Product.isEnabled:', array('Product.isEnabled' => 1));
		$this->applyInventoryFilter($filter);

		return $filter;
	}

	public function setProductCondition(\Phalcon\Mvc\Model\Query\Builder $query, $includeSubcategories = false)
	{
		if ($includeSubcategories)
		{
			if (!$this->isRoot())
			{
				$subQuery = 'SUBQUERY("SELECT COUNT(*) FROM ProductCategory LEFT JOIN Category ON ProductCategory.categoryID=Category.ID WHERE ProductCategory.productID=Product.ID AND Category.lft>=' . $this->lft . ' AND Category.rgt<=' . $this->rgt . '") > 0';
				$query->andWhere('(category\Category.lft >= :lft: AND category\Category.rgt <= :rgt:) OR ' . $subQuery, array('lft' => $this->lft, 'rgt' => $this->rgt));
			}
			else
			{
				$query->andWhere('product\Product.categoryID IS NOT NULL');
			}
		}
		else
		{
			$subQuery = '(SUBQUERY("SELECT COUNT(*) FROM ProductCategory WHERE ProductCategory.productID=Product.ID AND ProductCategory.categoryID=' . $this->getID() . '") > 0)';
			$query->andWhere('(Product.categoryID = :id:) OR ' . $subQuery, array('id' => $this->getID()));
		}
	}

	private function hasProductsAsSecondaryCategory()
	{
		return false;
		if (!isset($this->hasAsSecondary))
		{
			$this->hasAsSecondary = $this->getRelatedRecordCount('ProductCategory');
		}

		return $this->hasAsSecondary;
	}

	public function getProductFilter(ARSelectFilter $filter)
	{
		$filter->andWhere('Product.isEnabled = :Product.isEnabled:', array('Product.isEnabled' => 1));

		$this->applyInventoryFilter($filter);

		return $filter;
	}

	private function applyInventoryFilter(ARSelectFilter $filter)
	{
		$c = $this->getConfig();
		if ($c->get('INVENTORY_TRACKING') == 'ENABLE_AND_HIDE')
		{
			$cond = new MoreThanCond('Product.stockCount', 0);
			$cond->addOr('Product.isBackOrderable = :Product.isBackOrderable:', array('Product.isBackOrderable' => 1));
			$filter->andWhere($cond);
		}
	}

	public function getFilterSet()
	{
		if ($this->filterSetCache)
		{
			return $this->filterSetCache;
		}

		Classloader::import('application/model/filter/Filter');
		Classloader::import('application/model/filter/SelectorFilter');

		// get filter groups
		$groups = $this->getFilterGroupArray();

		$ids = array();
		$specFields = array();
		$filterGroups = array();
		foreach ($groups as $group)
		{
			if (in_array($group['SpecField']['type'],
			  			 array(SpecField::TYPE_NUMBERS_SELECTOR, SpecField::TYPE_TEXT_SELECTOR)))
			{
				$specFields[] = $group['SpecField']['ID'];
				$filterGroups[$group['SpecField']['ID']] = $group['ID'];
				$ids[] = $group['ID'];
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
			$filterCond = new INCond('Filter.filterGroupID', $ids);
			$filterFilter = new ARSelectFilter();
			$filterFilter->setCondition($filterCond);
			$filterFilter->orderBy('Filter.filterGroupID');
			$filterFilter->orderBy('Filter.position');

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
			$selectFilter->setCondition(new INCond('SpecFieldValue.specFieldID', $specFields));
			$selectFilter->orderBy('SpecFieldValue.specFieldID');
			$selectFilter->orderBy('SpecFieldValue.position');

			$specFieldValues = ActiveRecord::getRecordSet('SpecFieldValue', $selectFilter);
			foreach ($specFieldValues as $value)
			{
				$ret[] = new SelectorFilter($value, FilterGroup::getInstanceByID($filterGroups[$value->specField->getID()]));
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
	  	ClassLoader::import('application/model/filter/FilterGroup');
		$filter = $this->getFilterGroupFilter($includeParentFields);
		if (!$filter)
		{
		  	return new ARSet(null);
		}
		return ActiveRecord::getRecordSet('FilterGroup', $filter, array('SpecField', 'SpecFieldGroup'));
	}

	/**
	 * Returns a set of category filters
	 *
	 * @param bool $loadReferencedRecords
	 * @return ARSet
	 */
	public function getFilterGroupArray()
	{
		if (null === $this->filterGroupArrayCache)
		{
		  	ClassLoader::import('application/model/filter/FilterGroup');
			$filter = $this->getFilterGroupFilter();
			if (!$filter)
			{
				$this->filterGroupArrayCache = array();
			}
			else
			{
				$this->filterGroupArrayCache = ActiveRecord::getRecordSetArray('FilterGroup', $filter, array('SpecField'));
			}
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
		$filter->orderBy(new ARFieldHandle("SpecField", "categoryID"));
		$filter->orderBy(new ARFieldHandle("FilterGroup", "position"));

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
	  	ClassLoader::import('application/model/category/CategoryImage');

		return ActiveRecord::getRecordSet('CategoryImage', $this->getCategoryImagesFilter());
	}

	private function getCategoryImagesFilter()
	{
		$filter = new ARSelectFilter();
		$filter->setCondition('CategoryImage.categoryID = :CategoryImage.categoryID:', array('CategoryImage.categoryID' => $this->getID()));
		$filter->orderBy('CategoryImage.position', 'ASC');

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
		ClassLoader::import("application/model/category/SpecFieldGroup");
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
		ClassLoader::import("application/model/category/SpecFieldGroup");

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
		return ActiveRecordModel::getRecordSet('SpecField', $this->getSpecificationFilter($includeParentFields), true);
	}

	public function getSpecificationFieldArray($includeParentFields = true, $loadReferencedRecords = false)
	{
		return ActiveRecordModel::getRecordSetArray('SpecField', $this->getSpecificationFilter($includeParentFields), array('SpecFieldGroup'));
	}

	public function getSpecFieldsWithGroupsArray()
	{
		return ActiveRecordGroup::mergeGroupsWithFields('SpecFieldGroup', $this->getSpecificationFieldGroupArray(), $this->getSpecificationFieldArray(false, true));
	}

	public function getOptions($includeInheritedOptions = false)
	{
		ClassLoader::import('application/model/product/ProductOption');
		$f = new ARSelectFilter();

		if ($includeInheritedOptions)
		{
			$ids = array();
			foreach(array_reverse($this->getPathNodeArray(true)) as $cat)
			{
				$ids[] = $cat['ID'];
				$f->orderBy(new ARExpressionHandle('ProductOption.categoryID=' . $cat['ID']), 'DESC');
			}

			$f->setCondition(new INCond('ProductOption.categoryID', $ids));
		}
		else
		{
			$f->setCondition('ProductOption.categoryID = :ProductOption.categoryID:', array('ProductOption.categoryID' => $this->getID()));
		}

		$f->orderBy('ProductOption.position', 'ASC');

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

		$filter->orderBy(new ARFieldHandle("SpecFieldGroup", "position"), 'ASC');
		$filter->orderBy(new ARFieldHandle("SpecField", "position"), 'ASC');

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
		ClassLoader::import("application/model/category/SpecFieldGroup");

		$filter = new ARSelectFilter();
		$filter->orderBy(new ARFieldHandle("SpecFieldGroup", "position"), ARSelectFilter::ORDER_ASC);
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
return;
		ClassLoader::import("application/model/product/Product");

		//self::beginTransaction();

		$conditions = array('totalProductCount' => '',
							'activeProductCount' => 'Product.isEnabled = 1',
							'availableProductCount' => ' Product.isEnabled = 1 AND (stockCount > 0 OR type = ' . Product::TYPE_DOWNLOADABLE .  ')');

		$fields = array('totalProductCount', 'activeProductCount', 'availableProductCount');

		$queries = array();
		foreach ($conditions as $field => $condition)
		{
			$index = array_search($field, $fields);
			$queries[] = 'SELECT categoryID, COUNT(*), ' . $index . ' FROM Product ' . $condition . ' GROUP BY categoryID';
			$queries[] = 'SELECT ProductCategory.categoryID, COUNT(*), ' . $index . ' FROM ProductCategory LEFT JOIN Product ON Product.ID=ProductCategory.productID ' . $condition . ' GROUP BY ProductCategory.categoryID';
		}

		$basicCounts = implode(' UNION ', $queries);

		echo $basicCounts;exit;

		// reset counts to 0
		$sql = 'UPDATE Category SET ';
		foreach ($fields as $field)
		{
			$sql .= $field . '=0' . ('availableProductCount' != $field ? ',' : '');
		}

		self::executeUpdate($sql);

		// category product counts
		$categoryCond = '((categoryID = Category.ID) OR ((SELECT COUNT(*) FROM ProductCategory WHERE ProductCategory.categoryID=Category.ID AND ProductCategory.productID=Product.ID) > 0))';
		$sql = 'UPDATE Category SET totalProductCount = (SELECT COUNT(*) FROM Product WHERE ' . $categoryCond . '),
									activeProductCount = (SELECT COUNT(*) FROM Product WHERE ' . $categoryCond . ' AND Product.isEnabled = 1),
									availableProductCount = (SELECT COUNT(*) FROM Product WHERE ' . $categoryCond . ' AND Product.isEnabled = 1 AND (stockCount > 0 OR type = ' . Product::TYPE_DOWNLOADABLE .  '))';
		self::executeUpdate($sql);

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

		self::executeUpdate($sql);

		// additional categories
		$q = 'SELECT
				COUNT(DISTINCT productID)
				FROM ProductCategory
				LEFT JOIN Category AS jCat ON ProductCategory.categoryID=jCat.ID
				LEFT JOIN Product ON Product.ID=ProductCategory.productID
				LEFT JOIN Category AS mainCat ON Product.categoryID=mainCat.ID
				WHERE
					NOT (mainCat.lft >= Category.lft
					AND
					mainCat.rgt <= Category.rgt)
					AND
					jCat.lft >= Category.lft
					AND
					jCat.rgt <= Category.rgt';

		$sql = 'UPDATE CategoryCount LEFT JOIN Category ON CategoryCount.ID=Category.ID SET CategoryCount.totalProductCount = CategoryCount.totalProductCount + (' . $q . '),
									CategoryCount.activeProductCount = CategoryCount.activeProductCount + (' . $q . ' AND Product.isEnabled = 1),
									CategoryCount.availableProductCount = CategoryCount.availableProductCount + (' . $q . ' AND Product.isEnabled = 1 AND (stockCount > 0 OR type = ' . Product::TYPE_DOWNLOADABLE .  '))';
		//self::executeUpdate($sql);

		$sql = 'UPDATE Category LEFT JOIN CategoryCount ON Category.ID=CategoryCount.ID SET ';
		foreach ($fields as $field)
		{
			$sql .= 'Category.' . $field . '=CategoryCount.' . $field . ('availableProductCount' != $field ? ',' : '');
		}

		self::executeUpdate($sql);
		self::executeUpdate('DROP TEMPORARY TABLE CategoryCount');

		self::updateCategoryIntervals();

		//self::commit();
	}

	public static function old_recalculateProductsCount()
	{
		ClassLoader::import("application/model/product/Product");

		self::beginTransaction();

		$fields = array('totalProductCount', 'activeProductCount', 'availableProductCount');

		// reset counts to 0
		$sql = 'UPDATE Category SET ';
		foreach ($fields as $field)
		{
			$sql .= $field . '=0' . ('availableProductCount' != $field ? ',' : '');
		}

		self::executeUpdate($sql);

		// category product counts
		$sql = 'UPDATE Category SET totalProductCount = (SELECT COUNT(*) FROM Product WHERE categoryID = Category.ID),
									activeProductCount = (SELECT COUNT(*) FROM Product WHERE categoryID = Category.ID AND Product.isEnabled = 1),
									availableProductCount = (SELECT COUNT(*) FROM Product WHERE categoryID = Category.ID AND Product.isEnabled = 1 AND (stockCount > 0 OR type = ' . Product::TYPE_DOWNLOADABLE .  '))';
		self::executeUpdate($sql);

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

		self::executeUpdate($sql);

		// additional categories
		$q = 'SELECT
				COUNT(DISTINCT productID)
				FROM ProductCategory
				LEFT JOIN Category AS jCat ON ProductCategory.categoryID=jCat.ID
				LEFT JOIN Product ON Product.ID=ProductCategory.productID
				LEFT JOIN Category AS mainCat ON Product.categoryID=mainCat.ID
				WHERE
					NOT (mainCat.lft >= Category.lft
					AND
					mainCat.rgt <= Category.rgt)
					AND
					jCat.lft >= Category.lft
					AND
					jCat.rgt <= Category.rgt';

		$sql = 'UPDATE CategoryCount LEFT JOIN Category ON CategoryCount.ID=Category.ID SET CategoryCount.totalProductCount = CategoryCount.totalProductCount + (' . $q . '),
									CategoryCount.activeProductCount = CategoryCount.activeProductCount + (' . $q . ' AND Product.isEnabled = 1),
									CategoryCount.availableProductCount = CategoryCount.availableProductCount + (' . $q . ' AND Product.isEnabled = 1 AND (stockCount > 0 OR type = ' . Product::TYPE_DOWNLOADABLE .  '))';
		self::executeUpdate($sql);

		$sql = 'UPDATE Category LEFT JOIN CategoryCount ON Category.ID=CategoryCount.ID SET ';
		foreach ($fields as $field)
		{
			$sql .= 'Category.' . $field . '=CategoryCount.' . $field . ('availableProductCount' != $field ? ',' : '');
		}

		self::executeUpdate($sql);
		self::executeUpdate('DROP TEMPORARY TABLE CategoryCount');

		self::updateCategoryIntervals();

		self::commit();
	}

	public static function updateCategoryIntervals($productID = null)
	{
		$sql = "UPDATE Product
					LEFT JOIN (
						SELECT productID, GROUP_CONCAT(CONCAT(lft,'-',rgt) SEPARATOR ',') AS intervals
							FROM ProductCategory
							LEFT JOIN Category ON categoryID=ID GROUP BY productID) AS intv
						ON productID=Product.ID
					LEFT JOIN Category ON Product.categoryID=Category.ID
					SET categoryIntervalCache=CONCAT(Category.lft,'-',Category.rgt,',',COALESCE(intervals,''))";

		if ($productID)
		{
			if (is_object($productID))
			{
				$productID = $productID->getID();
			}

			if (is_numeric($productID)) // strings would require escaping anyway..
			{
				$sql .= ' WHERE Product.ID=' . $productID;
			}
		}
		self::executeUpdate($sql);
	}

	// subcategory counts
	private static function updateProductCount(Category $category)
	{
		$countTotal = $countAvailable = $countActive = 0;
		foreach ($category->getSubCategorySet() as $sub)
		{
			self::updateProductCount($sub);
			$countTotal += $sub->totalProductCount;
			$countAvailable += $sub->availableProductCount;
			$countActive += $sub->activeProductCount;
		}

		$category->totalProductCount = $category->totalProductCount + $countTotal;
		$category->activeProductCount = $category->activeProductCount + $countActive;
		$category->availableProductCount = $category->availableProductCount + $countAvailable;
		$category->save();
	}
}

?>
