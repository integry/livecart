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
	public function getSpecificationFieldSet($includeParentFields = false)
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

		return SpecField::getRecordSetArray($filter);
	}

	/**
	 * Gets a list of products assigned to this node
	 *
	 */
	public function getProductSet()
	{
		$this->getID();
		$productFilter = new ARSelectFilter();
		$productFilter->setCondition();
		$products = ActiveRecord::getRecordSet("Product", $productFilter);

		return $products;
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
	 * Creates array representation
	 *
	 * @return array
	 */
	public function toArray()
	{
		$store = Store::getInstance();
		$defaultLangCode = $store->getDefaultLanguageCode();

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
	public static function getNewInstance(ActiveTreeNode $parent)
	{
		return parent::getNewInstance(__CLASS__, $parent);
	}
}

?>