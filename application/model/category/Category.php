<?php
ClassLoader::import("application.model.system.ActiveTreeNode");
ClassLoader::import("application.model.system.MultilingualObjectInterface");


/**
 * Hierarchial product category model class
 *
 * @package application.model.category
 */
class Category extends ActiveTreeNode implements MultilingualObjectInterface
{
	const INCLUDE_PARENT = true;
	
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

		$schema->registerField(new ARField("name", ARArray::instance()));
		$schema->registerField(new ARField("description", ARArray::instance()));
		$schema->registerField(new ARField("keywords", ARArray::instance()));
		$schema->registerField(new ARField("isEnabled", ARBool::instance()));
		$schema->registerField(new ARField("handle", ARVarchar::instance(40)));
		$schema->registerField(new ARField("activeProductCount", ARInteger::instance()));
		$schema->registerField(new ARField("totalProductCount", ARInteger::instance()));
	}

	public function getProducts(ProductFilter $productFilter)
	{
		$filter = $productFilter->getSelectFilter();

		$cond = new EqualsCond(new ARFieldHandle('Product', 'categoryID'), $this->getID());
		$filterCond = $filter->getCondition();
		if ($filterCond)
		{
			$cond->addAND($filterCond);		  
		}
		$filter->setCondition($cond);
		
		return ActiveRecordModel::getRecordSet('Product', $filter);
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
		// load product specification
		$specFields = $this->getSpecificationFieldSet(self::INCLUDE_PARENT);	  
					
		$filter->joinTable('ProductPrice', 'Product', 'productID AND pricetable_EUR.currencyID = "EUR"', 'ID', 'pricetable_EUR');				  	
	  	$filter->addField('price', 'pricetable_EUR', 'price_EUR');

		return $filter;
	}

	public function setValueByLang($fieldName, $langCode, $value)
	{
		$valueArray = $this->getFieldValue($fieldName);
		if (!is_array($valueArray)) {
			$valueArray = array();
		}
		$valueArray[$langCode] = $value;
		$this->setFieldValue($fieldName, $valueArray);
	}

	public function getValueByLang($fieldName, $langCode, $returnDefaultIfEmpty = true)
	{
		$valueArray = $this->getFieldValue($fieldName);
		return $valueArray[$langCode];
	}

	public function setValueArrayByLang($fieldNameArray, $defaultLangCode, $langCodeArray, Request $request)
	{
		foreach ($fieldNameArray as $fieldName)
		{
			foreach ($langCodeArray as $langCode)
			{
				if ($langCode == $defaultLangCode)
				{
					$requestVarName = $fieldName;
				}
				else
				{
					$requestVarName = $fieldName . "_" . $langCode;
				}
				if ($request->isValueSet($requestVarName))
				{
					$this->setValueByLang($fieldName, $langCode, $request->getValue($requestVarName));
				}
			}
		}
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
		// get group filters
	  	$groups = $this->getFilterGroupSet();

		$ids = array();
		foreach ($groups as $group)
		{
		  	$ids[] = $group->getID();
		}		
		
		if (!$ids)
		{
		  	return false;
		}
		
		$filterCond = new INCond(new ARFieldHandle('Filter', 'filterGroupID'), $ids);
		$filterFilter = new ARSelectFilter();
		$filterFilter->setCondition($filterCond);
		$filterFilter->setOrder(new ARFieldHandle('Filter', 'filterGroupID'));
		$filterFilter->setOrder(new ARFieldHandle('Filter', 'position'));
		
		return ActiveRecord::getRecordSet('Filter', $filterFilter, true);	  	
	}
	
	/**
	 * Returns a set of category filters
	 *
	 * @param bool $loadReferencedRecords
	 * @return ARSet
	 */
	public function getFilterGroupSet($includeParentFields = true)
	{
	  	ClassLoader::import('application.model.category.FilterGroup');
		$filter = $this->getFilterGroupFilter($includeParentFields);
		if (!$filter)
		{
		  	return new ARSet(null);
		}
		return ActiveRecord::getRecordSet('FilterGroup', $filter, true);
	}

	private function getFilterGroupFilter($includeParentFields = true)
	{
		$fields = $this->getSpecificationFieldArray($includeParentFields);
		$categories = array();
		$ids = array();
		foreach ($fields as $field)
		{
		  	$ids[$field['ID']] = true;
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
	  	$cond = new EqualsCond(new ARFieldHandle('Category', 'parentNodeID'), $this->category->get()->getID());
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
	public function toArray()
	{
		$store = Store::getInstance();
		$defaultLangCode = $store->getDefaultLanguageCode();
		$currentLangCode = $store->getLocaleCode();

		$data = parent::toArray();
		$transformedData = array();
		$schema = self::getSchemaInstance(get_class($this));
		foreach ($data as $name => $value)
		{
			if (is_array($value))
			{
				if ($schema->getField($name)->getDataType() instanceof ARArray)
				{
					foreach ($value as $langCode => $multilingualValue)
					{
						if ($langCode != $defaultLangCode)
						{
							$transformedData[$name . "_" . $langCode] = $multilingualValue;
						}
						else
						{
							$transformedData[$name] = $multilingualValue;
						}
					}
				}

				// value in active language (default language value is used
				// if there's no value in active language)
				$transformedData[$name . '_lang'] = !empty($transformedData[$name . '_' . $currentLangCode]) ?
													$transformedData[$name . '_' . $currentLangCode] :
													(isset($transformedData[$name]) ? $transformedData[$name] : '');
			}
			else
			{
				$transformedData[$name] = $value;
			}
		}
		return $transformedData;
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
		$isEnabled = $this->isEnabled->get();
		if ($isEnabled)
		{
			return true;
		}
		else
		{
			return false;
		}
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
					$node->setFieldValue("activeProductCount", $activeProductCountUpdateStr);
					$node->save();
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
			$totalProductCpunt = $category->getFieldValue("totalProductCount");

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

    public function getBranch() {
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
		return SpecField::getRecordSet($this->getSpecificationFilter($includeParentFields), $loadReferencedRecords);
	}

	public function getProductById($productId)
	{
	  	
	}

	public function getSpecificationFieldArray($includeParentFields = false, $loadReferencedRecords = false, $mergeWithEmptyGroups = false)
	{
		ClassLoader::import("application.model.category.SpecField");

        $specFields = SpecField::getRecordSetArray($this->getSpecificationFilter($includeParentFields), true);

        if($mergeWithEmptyGroups)
        {
            $groups = $this->getSpecificationFieldGroupArray(false, true);
            return $this->mergeWithEmptyGroups($specFields, $groups);
        }
        else
        {
            return $specFields;
        }
	}
	
	private function mergeWithEmptyGroups($specFields, $groups)
	{
        $specFieldsWithGroups = array();
        $groupNum = 0;
        $specFieldsNum = 0;
        $groupsCount = count($groups);
                
        // Specification fields without a group
        $specFieldsFound = 0;
        while(isset($specFields[$specFieldsNum]) && ($specFields[$specFieldsNum]['specFieldGroupID'] == ''))
        {
             $specFieldsWithGroups[] = $specFields[$specFieldsNum++];
             $specFieldsFound = 0;
        }
        if($specFieldsFound == 0) 
        {
            $specFieldsWithGroups[] = array('SpecFieldGroup' => array());
        }
        
        while($groupNum < $groupsCount)
        {         
             $specFieldsFound = 0;
             while(isset($specFields[$specFieldsNum]) && $specFields[$specFieldsNum]['specFieldGroupID'] <= $groups[$groupNum]['ID'])
             {
                 $specFieldsWithGroups[] = $specFields[$specFieldsNum++];
                  $specFieldsFound++;
             }
         
             if($specFieldsFound == 0) 
             {
                 $specFieldsWithGroups[] = array('SpecFieldGroup' => $groups[$groupNum]);
             }
             
             $groupNum++;
        }
        
        return $specFieldsWithGroups; 
	}
	
	/**
	 * Crates a select filter for specification fields related to category
	 *
	 * @param bool $includeParentFields
	 * @return ARSelectFilter
	 */
	private function getSpecificationFilter($includeParentFields)
	{
		$path = parent::getPathNodeSet(Category::INCLUDE_ROOT_NODE);
		$filter = new ARSelectFilter();
		$filter->joinTable('SpecFieldGroup', 'SpecField', 'ID', 'specFieldGroupID', 'SpecFieldGroup_2');
		$filter->setOrder(new ARFieldHandle("SpecFieldGroup", "position", "SpecFieldGroup_2"), 'ASC');
					
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