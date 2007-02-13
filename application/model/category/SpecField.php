<?php
ClassLoader::import("application.model.system.MultilingualObject");
ClassLoader::import("application.model.category.Category");
ClassLoader::import("application.model.category.SpecFieldValue");
ClassLoader::import("application.model.category.SpecFieldGroup");
ClassLoader::import('application.model.specification.*');

/**
 * Specification field class
 *
 * @package application.model.category
 */
class SpecField extends MultilingualObject
{
    const DATATYPE_TEXT = 1;
    const DATATYPE_NUMBERS = 2;

    const TYPE_NUMBERS_SELECTOR = 1;
    const TYPE_NUMBERS_SIMPLE = 2;

    const TYPE_TEXT_SIMPLE = 3;
    const TYPE_TEXT_ADVANCED = 4;
    const TYPE_TEXT_SELECTOR = 5;
    const TYPE_TEXT_DATE = 6;

    /**
     * Define SpecField database schema
     */
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("SpecField");
		
		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("specFieldGroupID", "SpecFieldGroup", "ID", "SpecFieldGroup", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("categoryID", "Category", "ID", "Category", ARInteger::instance()));
		$schema->registerField(new ARField("name", ARArray::instance()));
		$schema->registerField(new ARField("description", ARArray::instance()));
		$schema->registerField(new ARField("type", ARInteger::instance(2)));
		$schema->registerField(new ARField("dataType", ARInteger::instance(2)));
		$schema->registerField(new ARField("position", ARInteger::instance(2)));
		$schema->registerField(new ARField("handle", ARVarchar::instance(40)));
		$schema->registerField(new ARField("isMultiValue", ARBool::instance()));
		$schema->registerField(new ARField("isRequired", ARBool::instance()));
	}    
    
    /**
	 * Get instance SpecField record by id
	 *
	 * @param mixred $recordID Id
	 * @param bool $loadRecordData If true load data
	 * @param bool $loadReferencedRecords If true load references. And $loadRecordData is true load a data also
	 *
	 * @return  SpecField
	 */
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false)
	{
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords);
	}

	/**
	 * Get a new SpecField instance
	 *
	 * @param Category $category Category instance
	 * @param int $dataType Data type code (ex: self::DATATYPE_TEXT)
	 * @param int $type Field type code (ex: self::TYPE_TEXT_SIMPLE)
	 *
	 * @return  SpecField
	 */
	public static function getNewInstance(Category $category, $dataType = false, $type = false)
	{
		$specField = parent::getNewInstance(__CLASS__);
		$specField->category->set($category);

		if ($dataType)
		{
			$specField->dataType->set($dataType);
			$specField->type->set($type); 
		}

		return $specField;
	}

	/**
	 * Get a set of SpecField records
	 *
	 * @param ARSelectFilter $filter
	 * @param bool $loadReferencedRecords Load referenced tables data
	 *
	 * @return ActiveRecordSet
	 */
	public static function getRecordSet(ARSelectFilter $filter, $loadReferencedRecords = false)
	{
		return parent::getRecordSet(__CLASS__, $filter, $loadReferencedRecords);
	}

	/**
	 * Get a set of SpecField records
	 *
	 * @param ARSelectFilter $filter
	 * @param bool $loadReferencedRecords Load referenced tables data
	 *
	 * @return array
	 */
	public static function getRecordSetArray(ARSelectFilter $filter, $loadReferencedRecords = false)
	{
		return parent::getRecordSetArray(__CLASS__, $filter, $loadReferencedRecords);
	}

    /**
     * Adds a "choice" value to this field
     *
     * @param SpecFieldValue $value
     *
     * @todo calculate value position if needed
     */
    public function addValue(SpecFieldValue $value)
    {
		$value->specField->set($this);
		$value->save();
    }

    /**
     * Gets a related table name, where field values are stored
     *
     * @return array
     */
	public function getValueTableName()
	{
		switch ($this->type->get())  
		{
		  	case SpecField::TYPE_NUMBERS_SELECTOR:
		  	case SpecField::TYPE_TEXT_SELECTOR:
				return 'SpecificationItem';
				break;

		  	case SpecField::TYPE_NUMBERS_SIMPLE:
				return 'SpecificationNumericValue';
				break;

		  	case SpecField::TYPE_TEXT_SIMPLE:
		  	case SpecField::TYPE_TEXT_ADVANCED:			  				  	
				return 'SpecificationStringValue';
				break;

		  	case SpecField::TYPE_TEXT_DATE:
				return 'SpecificationDateValue';
				break;
				
			default:
				throw new Exception('Invalid specField type: ' . $this->type->get());
		}			
	  	
	}

	public function getFiltersGroupsListArray()
	{
		$filter = new ARSelectFilter();
		$filter->setOrder(new ARFieldHandle("FilterGroup", "position"));
		$filter->setCondition(new EqualsCond(new ARFieldHandle("FilterGroup", "specFieldID"), $this->getID()));

        $filterGroups = FilterGroup::getRecordSet($filter);
        $filterGroupsArray = array();
        $i = 0;
        foreach($filterGroups as $filter)
        {
            $filterGroupsArray[$i] = $filter->toArray(false, false);
            $filterGroupsArray[$i]['filtersCount']  = $filter->getFiltersList()->getTotalRecordCount();
            $i++;
        }
        
		return $filterGroupsArray;
	}

	/**
	 *  Creates an instance of specification field value depending of field type (a factory)
	 *  
	 *	For example, if the field is designated for storing numeric values, 
	 *  an instance of SpecificationNumericValue will be returned.
	 *
	 *  @param Product $product Product instance
	 *  @value mixed Field value (it may be a number, string, date or SpecFieldValue instance depending on field type)
	 */
	public function getNewSpecificationInstance(Product $product, $value)
	{
		$specValueClass = $this->getValueTableName();
		if ('SpecificationItem' == $specValueClass)
		{
			if ($this->isMultiValue->get())
			{
				$specValueClass = 'MultiValueSpecificationItem';	  	
			}
		}

		return call_user_func(array($specValueClass, 'getNewInstance'), $product, $this, $value);
	}

	/**
	 * This method is checking if SpecField record with passed id exist in the database
	 *
	 * @param int $id Record id
	 * @return bool
	 */
	public static function exists($id)
	{
	    return ActiveRecord::objectExists(__CLASS__, (int)$id);
	}

	/**
	 * Delete spec field from database
	 */
	public static function deleteById($id)
	{
	    return parent::deleteByID(__CLASS__, (int)$id);
	}

	/**
	 * Loads a set of spec field records in current category
	 *
	 * @return ARSet
	 */
	public function getValuesList()
	{
	    return SpecFieldValue::getRecordSetArray($this->getID());
	}

	/**
	 * Loads a set of spec field records in current category
	 *
	 * @return ARSet
	 */
	public function getValuesSet()
	{
		return SpecFieldValue::getRecordSet($this->getID());
	}

	/**
	 * Check if current specification field is selector type
	 *
	 * @return boolean
	 */
	public function isSelector()
	{
		return in_array($this->type->get(), SpecField::getSelectorValueTypes());  
	}
	
	/**
	 * Check if current specification field is text type
	 *
	 * @return boolean
	 */
	public function isTextField()
	{
		return in_array($this->type->get(), array(SpecField::TYPE_TEXT_SIMPLE, SpecField::TYPE_TEXT_ADVANCED));  
	}

	/**
	 * Get array of selector types
	 *
	 * @return array
	 */
	public static function getSelectorValueTypes()
	{
	    return array(self::TYPE_NUMBERS_SELECTOR, self::TYPE_TEXT_SELECTOR);
	}
	
	public static function getNumberTypes()
	{
	    return array(self::TYPE_NUMBERS_SELECTOR, self::TYPE_NUMBERS_SIMPLE);
	}
	
	public static function getTextTypes()
	{
	    return array(self::TYPE_TEXT_ADVANCED, self::TYPE_TEXT_ADVANCED, self::TYPE_TEXT_SELECTOR, self::TYPE_TEXT_DATE);
	}
	
	public static function getDataTypeFromType($type) 
	{
	    if(in_array($type, self::getTextTypes())) return self::DATATYPE_TEXT;
	    else return self::DATATYPE_NUMBERS;
	}

	/**
	 * Save specification field values in database
	 */
	public function saveValues($values, $type, $languageCodes) {
        $position = 1;
        foreach ($values as $key => $value)
        {
            if(preg_match('/^new/', $key))
            {
                $specFieldValues = SpecFieldValue::getNewInstance($this);
                $specFieldValues->setFieldValue('position', 100000);
            }
            else
            {
               $specFieldValues = SpecFieldValue::getInstanceByID((int)$key);
            }

            if($type == self::TYPE_NUMBERS_SELECTOR)
            {
                $specFieldValues->setFieldValue('value', $value);
            }
            else
            {
                $specFieldValues->setLanguageField('value', $value, $languageCodes);
            }

            $specFieldValues->setFieldValue('position', $position++);
            $specFieldValues->save();
        }
	}

	/**
	 * Transforms data array to a following format:
	 *
	 * simpleField => value,
	 * multilingualField_langCode => value,
	 * multilingualField2_langCode => otherValue, and etc.
	 *
	 */
	public function toArray($recursive = false, $convertToUnderscore = true)
    {
	  	$array = parent::toArray($recursive, $convertToUnderscore);
	  	$array['fieldName'] = $this->getFormFieldName();
	  	
	  	return $array;
	}

	public function getFormFieldName($language = false)
	{
	  	return 'specField_' . $this->getID() . ($language && (Store::getInstance()->getDefaultLanguageCode() != $language) ? '_' . $language : '');
	}

	/**
	 * Count specification fields in this category
	 *
	 * @param Category $category Category active record
	 * @return integer
	 */
    public static function countItems(Category $category)
    {
        return $category->getSpecificationFieldSet()->getTotalRecordCount();
    }
    
    /**
     *	Adds JOIN definition to ARSelectFilter to retrieve product attribute value for the particular SpecField
     *	
     *	@param	ARSelectFilter	$filter	Filter instance
     *	@return	string	Query field alias name
     */
	public function defineJoin(ARSelectFilter $filter)
    {
	  	$aliasTable = 'specTable_' . $this->getID();
	  	$aliasField = 'specField_' . $this->getID();
		$table = $this->getValueTableName();
		
		if ('SpecificationItem' != $table)
		{
			$filter->joinTable($table, 'Product', 'productID AND ' . $aliasTable . '.SpecFieldID = ' . $this->getID(), 'ID', $aliasTable);				  	
		}
		else
		{
		  	if ($this->isMultiValue->get())
		  	{
				$values = $this->getValuesSet();
				foreach ($values as $value)
				{
				  	$aliasTable = 'specTable_' . $this->getID() . '_' . $value->getID();
				  	$aliasField = 'multiItem_' . $this->getID() . '_' . $value->getID();
		
					$filter->joinTable('SpecFieldValue', 'Product', 'productID AND ' . $aliasTable . '.ID = ' . $value->getID(), 'ID', $aliasTable);				  	
				}
			}
			else
			{
				$specItemTable = 'specItemTable_' . $this->getID();
				$filter->joinTable('SpecificationItem', 'Product', 'productID AND ' . $specItemTable . '.SpecFieldID = ' . $this->getID(), 'ID', $specItemTable);				  				  
			  	$filter->addField('specFieldValueID', $specItemTable, 'specFieldValueID_' . $this->getID());
	
				$filter->joinTable('SpecFieldValue', $specItemTable, 'ID', 'SpecFieldValueID', $aliasTable);				  				  
		  	}
		}	  	
	
		$filter->addField('value', $aliasTable, $aliasField);
	
		return $aliasField;		  	
	}

    /**
     * Validates specification field form
     *
     * @param array $values List of values to validate.
     * @param array $config 
     * @return array List of all errors
     */
    public static function validate($values = array(), $languageCodes)
    {
        $errors = array();

        if(!isset($values['name']) || $values['name'][$languageCodes[0]] == '')
        {
            $errors["name[{$languageCodes[0]}]"] = '_error_name_empty';
        }

        if(!isset($values['handle']) || $values['handle'] == '' || preg_match('/[^\w\d_.]/', $values['handle']))
        {
            $errors['handle'] = '_error_handle_invalid';
        }
        else
        {
            $values['ID'] = !isset($values['ID']) ? -1 : $values['ID'];
            $filter = new ARSelectFilter();
                $handleCond = new EqualsCond(new ARFieldHandle('SpecField', 'handle'), $values['handle']);
                $handleCond->addAND(new EqualsCond(new ARFieldHandle('SpecField', 'categoryID'), (int)$values['categoryID']));
                $handleCond->addAND(new NotEqualsCond(new ARFieldHandle('SpecField', 'ID'), (int)$values['ID']));
            $filter->setCondition($handleCond);
            if(count(SpecField::getRecordSetArray($filter)) > 0)
            {
                $errors['handle'] =  '_error_handle_exists';
            }
        }

        if(!isset($values['handle']) || $values['handle'] == '')
        {
            $errors['handle'] = '_error_handle_empty';
        }

        if(in_array($values['type'], self::getSelectorValueTypes()) && isset($values['values']) && is_array($values['values']))
        {
            foreach ($values['values'] as $key => $v)
            {
                if(empty($v[$languageCodes[0]]))
                {
                    $errors["values[$key][{$languageCodes[0]}]"] = '_error_value_empty';
                }

                if(SpecField::getDataTypeFromType($values['type']) == 2 && !is_numeric($v[$languageCodes[0]]))
                {
                    $errors["values[$key][{$languageCodes[0]}]"] = '_error_value_is_not_a_number';
                }
            }
        }

        return $errors;
    }
}
?>