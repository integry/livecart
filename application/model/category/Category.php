<?php
ClassLoader::import("application.model.system.ActiveTreeNode");
ClassLoader::import("application.model.system.MultilingualObject");
ClassLoader::import("application.model.category.CategoryImage");

/**
 * Hierarchial product category model class
 *
 * @package application.model.category
 */
class Category extends ActiveTreeNode implements MultilingualObjectInterface
{
	const INCLUDE_PARENT = true;
	
	private $specFieldArrayCache = array();
	private $filterGroupArrayCache = array();
	
	/**
	 * Define database schema used by this active record instance
	 *
	 * @param string $className Schema name
	 */
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("Category");

		parent::defineSchema($className);

		$schema->registerField(new ARForeignKeyField("defaultImageID", "categoryImage", "ID", 'CategoryImage', ARInteger::instance()));
		$schema->registerField(new ARField("name", ARArray::instance()));
		$schema->registerField(new ARField("description", ARArray::instance()));
		$schema->registerField(new ARField("keywords", ARArray::instance()));
		$schema->registerField(new ARField("isEnabled", ARBool::instance()));
		$schema->registerField(new ARField("handle", ARVarchar::instance(40)));
		$schema->registerField(new ARField("availableProductCount", ARInteger::instance()));
		$schema->registerField(new ARField("activeProductCount", ARInteger::instance()));
		$schema->registerField(new ARField("totalProductCount", ARInteger::instance()));
	}

	public function getProducts(ProductFilter $productFilter, $loadReferencedRecords = false)
	{
		return ActiveRecordModel::getRecordSet('Product', $this->getProductsFilter($productFilter), $loadReferencedRecords);
	}
	
	public function getProductsArray(ProductFilter $productFilter, $loadReferencedRecords = false)
	{
		return ActiveRecordModel::getRecordSetArray('Product', $this->getProductsFilter($productFilter), $loadReferencedRecords);
	}
	
	public function getActiveProductCount()
	{
		$config = Config::getInstance();
		
		// all enabled products are available
		if ($config->getValue('DISABLE_INVENTORY') || !$config->getValue('DISABLE_NOT_IN_STOCK'))
		{
			return $this->activeProductCount->get();
		}
		
		// only enabled products that are in stock
		else
		{
			return $this->availableProductCount->get();
		}
	}
	
	public function getProductCount(ProductFilter $productFilter)
	{
		$query = new ARSelectQueryBuilder();
		$query->includeTable('Product');
		$query->addField('COUNT(*) AS cnt');
		$filter = $this->getProductsFilter($productFilter);
		$filter->setLimit(0);
		$query->setFilter($filter);		
		$data = ActiveRecord::getDataBySQL($query->createString());
		return $data[0]['cnt'];
	}

	/**
	 *	Create a basic ARSelectFilter object to select category products
	 */
	public function getProductsFilter(ProductFilter $productFilter)
	{
		$filter = $productFilter->getSelectFilter();		
		$filter->mergeCondition(new EqualsCond(new ARFieldHandle('Product', 'categoryID'), $this->getID()));		

		$config = Config::getInstance();
		if ($config->getValue('DISABLE_NOT_IN_STOCK') && !$config->getValue('DISABLE_INVENTORY'))
		{
			$cond = new MoreThanCond(new ARFieldHandle('Product', 'stockCount'), 0);
			$cond->addOR(new EqualsCond(new ARFieldHandle('Product', 'isBackOrderable'), 1));
			$filter->mergeCondition($cond);
		}

		return $filter;
	}
	
	public function testGetProductArray(ARSelectFilter $filter, $loadSpecification = false)
	{
		// get specification fields
		if ($loadSpecification)
		{
			$specFields = $this->getSpecificationFieldSet(self::INCLUDE_PARENT);	  
		}	  	
	
		ClassLoader::import('application.model.product.Product');
		
		$cond = new EqualsCond(new ARFieldHandle('Product', 'categoryID'), $this->getID());
		$filter->setCondition($cond);
	
		return ActiveRecordModel::getRecordSet('Product', $filter, true, true);
	
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
	 * Gets a list of products assigned to this node
	 *
	 * @param bool $loadReferencedRecords
	 * @return array
	 */
	public function getProductArray($loadReferencedRecords = false)
	{
		return $this->getRelatedRecordSetArray("Product", $this->getProductFilter(new ARSelectFilter()), $loadReferencedRecords);
	}

	public function getProductFilter(ARSelectFilter $filter)
	{
        $filter->mergeCondition(new EqualsCond(new ARFieldHandle('Product', 'isEnabled'), 1));
		
		$c = Config::getInstance();
		if (!$c->getValue('DISABLE_INVENTORY'))
		{
			if ($c->getValue('DISABLE_NOT_IN_STOCK'))
			{
				$cond = new MoreThanCond(new ARFieldHandle('Product', 'stockCount'), 0);
				$cond->addOr(new EqualsCond(new ARFieldHandle('Product', 'isBackOrderable'), 1));
				$filter->mergeCondition($cond);					
			}
		}
		
		return $filter;
	}

	public function setValueByLang($fieldName, $langCode, $value)
	{
		return MultiLingualObject::setValueByLang($fieldName, $langCode, $value);
	}

	public function getValueByLang($fieldName, $langCode, $returnDefaultIfEmpty = true)
	{
		return MultiLingualObject::getValueByLang($fieldName, $langCode, $returnDefaultIfEmpty);
	}

	public function setValueArrayByLang($fieldNameArray, $defaultLangCode, $langCodeArray, Request $request)
	{
		return MultiLingualObject::setValueArrayByLang($fieldNameArray, $defaultLangCode, $langCodeArray, $request);
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

	public function getFilterSet()
	{
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
			$this->specFieldArrayCache = $this->getSpecificationFieldArray(false);	
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

		$cond = new INCond(new ARFieldHandle("FilterGroup", "specFieldID"), implode(', ', $ids));

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
	 * Returns a set of direct subcategories
	 *
	 * @param bool $loadReferencedRecords
	 * @return ARSet
	 */
	public function getSubcategorySet($loadReferencedRecords = false)
	{
	  	return ActiveRecord::getRecordSet('Category', $this->getSubcategoryFilter(), $loadReferencedRecords);
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

	private function getSubcategoryFilter()
	{
	  	$filter = new ARSelectFilter();
	  	$cond = new EqualsCond(new ARFieldHandle('Category', 'parentNodeID'), $this->getID());
	  	$cond->addAND(new EqualsCond(new ARFieldHandle('Category', 'isEnabled'), 1));
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
	 * Creates array representation
	 *
	 * @return array
	 */
    protected static function transformArray($array, $className = __CLASS__)
	{
		return MultiLingualObject::transformArray($array, $className);
	}	

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
		return parent::getNewInstance(__CLASS__, $parent);
	}

	public function isEnabled()
	{
		$this->load();
		return $this->isEnabled->get();
	}

	protected function insert()
	{
		// set handle if empty
		if (!$this->handle->get())
		{
            $this->handle->set(Store::createHandleString($this->getValueByLang('name', Store::getInstance()->getDefaultLanguageCode())));    
        }
        
        return parent::insert();        
    }
    
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
			$activeProductCount = $this->getFieldValue("activeProductCount");
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
						$node->setFieldValue("activeProductCount", $activeProductCountUpdateStr);
						$node->save();						
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
	 * @return ActiveTreeNode
	 */
	public static function getRootNode()
	{
		return parent::getRootNode(__CLASS__);
	}

	/**
	 * Removes category by ID and fixes data in parent categories
	 * (updates activeProductCount and totalProductCount)
	 *
	 * @param int $recordID
	 */
	public static function deleteByID($recordID)
	{
		ActiveRecordModel::beginTransaction();

		try
		{
			$category = Category::getInstanceByID($recordID, Category::LOAD_DATA);
			$activeProductCount = $category->getFieldValue("activeProductCount");
			$totalProductCount = $category->getFieldValue("totalProductCount");

			$pathNodes = $category->getPathNodeSet(true);

			foreach ($pathNodes as $node)
			{
				$node->setFieldValue("activeProductCount", "activeProductCount - " . $activeProductCount);
				$node->setFieldValue("totalProductCount", "totalProductCount - " . $totalProductCount);

				$node->save();
			}
			ActiveRecordModel::commit();
			parent::deleteByID(__CLASS__, $recordID);
		}
		catch (Exception $e)
		{
			ActiveRecordModel::rollback();
			throw $e;
		}
	}

    public function getBranch() 
	{
        $filter = new ARSelectFilter();
        $filter->setOrder(new ARFieldHandle("Category", "lft", 'ASC'));
        $filter->setCondition(new OperatorCond(new ARFieldHandle("Category", "parentNodeID"), $this->getID(), "="));
			
		$categoryList = Category::getRecordSet($filter);
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
		
		return SpecFieldGroup::getRecordSetArray($this->getSpecificationGroupFilter(), $loadReferencedRecords);
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
		
		return SpecFieldGroup::getRecordSet($this->getSpecificationGroupFilter(), $loadReferencedRecords);
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
		return SpecField::getRecordSet($this->getSpecificationFilter($includeParentFields), true);
	}

	public function getProductById($productId)
	{
	  	
	}

	public function getSpecificationFieldArray($includeParentFields = false, $loadReferencedRecords = false)
	{
		ClassLoader::import("application.model.category.SpecField");
        $specFields = SpecField::getRecordSet($this->getSpecificationFilter($includeParentFields), array('SpecFieldGroup'))->toArray();

        return $specFields;
	}
	
	public function getSpecFieldsWithGroupsArray()
	{
	    return SpecFieldGroup::mergeGroupsWithFields($this->getSpecificationFieldGroupArray(), $this->getSpecificationFieldArray(false, true));
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
		$path = parent::getPathNodeSet(Category::INCLUDE_ROOT_NODE);
		$filter = new ARSelectFilter();
		
		$filter->setOrder(new ARFieldHandle("SpecFieldGroup", "position"), 'ASC');
		$filter->setOrder(new ARFieldHandle("SpecField", "position"), 'ASC');
							
		$cond = new EqualsCond(new ARFieldHandle("SpecField", "categoryID"), $this->getID());

		if ($includeParentFields)
		{
			foreach ($path as $node)
			{
				$cond->addOR(new EqualsCond(new ARFieldHandle("SpecField", "categoryID"), $node->getID()));
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

}

?>