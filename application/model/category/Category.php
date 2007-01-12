<?php
ClassLoader::import("application.model.system.ActiveTreeNode");
ClassLoader::import("application.model.system.MultilingualObjectInterface");


/**
 * Product category model class
 *
 * Product categories are organized and stored as a tree in a database
 *
 * @author Saulius Rupainis <saulius@integry.net>
 * @package application.model.category
 *
 */
class Category extends ActiveTreeNode implements MultilingualObjectInterface
{
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
		$schema->registerField(new ARField("position", ARInteger::instance()));
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

	public function getSpecificationFieldArray($includeParentFields = false, $loadReferencedRecords = false)
	{
		ClassLoader::import("application.model.category.SpecField");
		return SpecField::getRecordSetArray($this->getSpecificationFilter($includeParentFields), $loadReferencedRecords);
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
		$filter->setOrder(new ARFieldHandle("SpecField", "categoryID"));
		$filter->setOrder(new ARFieldHandle("SpecField", "position"));

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
	 * Gets a list of products assigned to this node
	 * @param bool $loadReferencedRecords
	 *
	 * @return ARSet
	 */
	public function getProductSet($loadReferencedRecords = false)
	{
		return $this->getRelatedRecordSet("Product", $this->getProctFilter(), $loadReferencedRecords);
	}

	/**
	 * Gets a list of products assigned to this node
	 *
	 * @param bool $loadReferencedRecords
	 * @return array
	 */
	public function getProductArray($loadReferencedRecords = false)
	{
		return $this->getRelatedRecordSetArray("Product", $this->getProductFilter(), $loadReferencedRecords);
	}

	private function getProductFilter()
	{
		$filter = new ARSelectFilter();
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

	/**
	 * Returns a set of category filters
	 *
	 * @param bool $loadReferencedRecords
	 * @return ARSet
	 */
	public function getFilterGroupSet($includeParentFields = true)
	{
	  	ClassLoader::import('application.model.category.FilterGroup');
		$filter = $this->getFilterGroupFilter();
		if (!$filter)
		{
		  	return false;
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
	  	$filter->setOrder(new ARFieldHandle('Category', 'position'), 'ASC');

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
	  	$filter->setOrder(new ARFieldHandle('Category', 'position'), 'ASC');

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

					// value in active language (default language value is used 
					// if there's no value in active language)
					$transformedData[$name . '_lang'] = !empty($transformedData[$name . '_' . $currentLangCode]) ?
														$transformedData[$name . '_' . $currentLangCode] :
														isset($transformedData[$name]) ? $transformedData[$name] : '';			
				}
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

	protected function update()
	{
		return parent::update();
	}

	protected function insert()
	{
		return parent::insert();
	}

	public static function getRootNode()
	{
		return parent::getRootNode(__CLASS__);
	}

	public static function deleteByID($recordID)
	{
		return parent::deleteByID(__CLASS__, $recordID);
	}

}

?>