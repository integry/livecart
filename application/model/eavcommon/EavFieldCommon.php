<?php


/**
 * Specification attributes allow to define specific product models with a specific set of features or parameters.
 *
 * Each SpecField is a separate attribute. For example, screen size for laptops, ISBN code for books,
 * horsepowers for cars, etc. Since SpecFields are linked to categories, products from different categories can
 * have different set of attributes.
 *
 * @package application/model/eavcommon
 * @author Integry Systems <http://integry.com>
 */
abstract class EavFieldCommon extends MultilingualObject
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
	 * Referenced class name (for example, Product)
	 */
	public abstract function getOwnerClass();

	public abstract function getStringValueClass();

	public abstract function getNumericValueClass();

	public abstract function getDateValueClass();

	public abstract function getSelectValueClass();

	public abstract function getMultiSelectValueClass();

	public abstract function getFieldIDColumnName();

	public abstract function getObjectIDColumnName();

	public abstract function getOwnerIDColumnName();

	protected abstract function getParentCondition();

	public static function getGroupIDColumnName($className)
	{
		$group = $className . 'Group';
		return strtolower(substr($group, 0, 1)) . substr($group, 1) . 'ID';
	}

	/**
	 * Define database schema
	 */
	public static function defineSchema($className)
	{
				$schema->setName($className);

		public $ID;

		$group = $className . 'Group';
		$schema->registerField(new ARForeignKeyField(strtolower(substr($group, 0, 1)) . substr($group, 1) . 'ID', $group, "ID", $group, ARInteger::instance()));
		public $name;
		public $description;
		public $type;
		public $dataType;
		public $position;
		public $handle;
		public $isMultiValue;
		public $isRequired;
		public $isDisplayed;
		public $isDisplayedInList;
		public $valuePrefix;
		public $valueSuffix;

		return $schema;
	}

	/**
	 * Get a new EavFieldCommon instance
	 *
	 * @param string $className Instance class name
	 * @param int $dataType Data type code (ex: self::DATATYPE_TEXT)
	 * @param int $type Field type code (ex: self::TYPE_TEXT_SIMPLE)
	 *
	 * @return  EavFieldCommon
	 */
	public static function getNewInstance($className, $dataType = false, $type = false)
	{
		$field = parent::getNewInstance($className);

		if ($dataType)
		{
			$field->dataType = $dataType;
			$field->type = $type;
		}

		return $field;
	}

	public function getNewValueInstance()
	{
		$class = call_user_func(array($this->getSelectValueClass(), 'getValueClass'));
		return call_user_func_array(array($class, 'getNewInstance'), array($this));
	}

	public function getValueInstanceByID($id, $loadData = false)
	{
		$class = call_user_func(array($this->getSelectValueClass(), 'getValueClass'));
		return call_user_func_array(array($class, 'getInstanceByID'), array($id, $loadData));
	}

	/*####################  Value retrieval and manipulation ####################*/

	/**
	 * Adds a 'choice' value to this field
	 *
	 * @param SpecFieldValue $value
	 */
	public function addValue(EavValueCommon $value)
	{
		$value->getField() = $this;
		$value->save();
	}

	/**
	 * Gets a related table name, where field values are stored
	 *
	 * @return array
	 */
	public function getValueTableName()
	{
		if (!$this->isLoaded())
		{
			$this->load();
		}

		switch ($this->type->get())
		{
		  	case self::TYPE_NUMBERS_SELECTOR:
		  	case self::TYPE_TEXT_SELECTOR:
				return $this->getSelectValueClass();
				break;

		  	case self::TYPE_NUMBERS_SIMPLE:
				return $this->getNumericValueClass();
				break;

		  	case self::TYPE_TEXT_SIMPLE:
		  	case self::TYPE_TEXT_ADVANCED:
				return $this->getStringValueClass();
				break;

		  	case self::TYPE_TEXT_DATE:
				return $this->getDateValueClass();
				break;

			default:
			print_r($this->toArray());exit;
				throw new Exception('Invalid field type: ' . $this->type->get());
		}
	}

	public function getSpecificationFieldClass()
	{
		$specValueClass = $this->getValueTableName();
		if ($this->getSelectValueClass() == $specValueClass)
		{
			if ($this->isMultiValue->get())
			{
				$specValueClass = $this->getMultiSelectValueClass();
			}
		}

		return $specValueClass;
	}

	/**
	 * Check if current specification field is selector type
	 *
	 * @return boolean
	 */
	public function isSelector()
	{
		return in_array($this->type->get(), self::getSelectorValueTypes());
	}

	/**
	 * Check if current specification field is text type
	 *
	 * @return boolean
	 */
	public function isTextField()
	{
		return in_array($this->type->get(), array(self::TYPE_TEXT_SIMPLE, self::TYPE_TEXT_ADVANCED));
	}

	/**
	 * Check if current specification field type is simple numbers
	 *
	 * @return boolean
	 */
	public function isSimpleNumbers()
	{
		return $this->type->get() == self::TYPE_NUMBERS_SIMPLE;
	}

	public function isNumeric()
	{
		return $this->dataType->get() == self::DATATYPE_NUMBERS;
	}

	/**
	 * Check if current specification field type is date
	 *
	 * @return boolean
	 */
	public function isDate()
	{
		return $this->type->get() == self::TYPE_TEXT_DATE;
	}

	public function getGroup()
	{
		$group = get_class($this) . 'Group';
		$var = strtolower(substr($group, 0, 1)) . substr($group, 1);
		return $this->$var;
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
		return array(self::TYPE_TEXT_SIMPLE, self::TYPE_TEXT_ADVANCED, self::TYPE_TEXT_SELECTOR, self::TYPE_TEXT_DATE);
	}

	public static function getMultilanguageTypes()
	{
		return array(self::TYPE_TEXT_SIMPLE, self::TYPE_TEXT_ADVANCED, self::TYPE_TEXT_SELECTOR);
	}

	public static function getDataTypeFromType($type)
	{
		if(in_array($type, self::getTextTypes())) return self::DATATYPE_TEXT;
		else return self::DATATYPE_NUMBERS;
	}

	public function getJoinAlias()
	{
		return 'specField_' . $this->getID();
	}

	public function getFieldHandle($field)
	{
		return new ARExpressionHandle($this->getJoinAlias() . '.' . $field);
	}

	public function getFormFieldName($language = false)
	{
	  	return 'specField_' . $this->getID() . ($language && (self::getApplication()->getDefaultLanguageCode() != $language) ? '_' . $language : '');
	}

	/**
	 *	Adds JOIN definition to ARSelectFilter to retrieve product attribute value for the particular SpecField
	 *
	 *	@param	ARSelectFilter	$filter	Filter instance
	 */
	public function defineJoin(ARSelectFilter $filter)
	{
		$table = $this->getJoinAlias();
		$filter->joinTable($this->getValueTableName(), $this->getOwnerClass(), $this->getObjectIDColumnName() . ' AND ' . $table . '.' . $this->getFieldIDColumnName() . ' = ' . $this->getID(), 'ID', $table);

		if ($this->isSelector() && !$this->isMultiValue->get())
		{
			$itemClass = $this->getSelectValueClass();
			$valueClass = call_user_func(array($itemClass, 'getValueClass'));
			$valueField = call_user_func(array($itemClass, 'getValueIDColumnName'));
			$filter->joinTable($valueClass, $table, 'ID', $valueField, $table . '_value');
		}
	}

	/*####################  Saving ####################*/

	protected function insert()
	{
		// get max position
	  	$f = new ARSelectFilter();
	  	$f->setCondition($this->getParentCondition());
	  	$f->setOrder(new ARFieldHandle(get_class($this), 'position'), 'DESC');
	  	$f->setLimit(1);
	  	$rec = ActiveRecord::getRecordSetArray(get_class($this), $f);
		$position = (is_array($rec) && count($rec) > 0) ? $rec[0]['position'] + 1 : 1;

		$this->position = $position;

		return parent::insert();
	}

	/*####################  Get related objects ####################*/

	/**
	 * Loads a set of spec field records in current category
	 *
	 * @return array
	 */
	public function getValuesList()
	{
		$valueClass = call_user_func(array($this->getSelectValueClass(), 'getValueClass'));
		return call_user_func(array($valueClass, 'getRecordSetArray'), $this->getID());
	}

	/**
	 * Loads a set of spec field records in current category
	 *
	 * @return ARSet
	 */
	public function getValuesSet()
	{
		$valueClass = call_user_func(array($this->getSelectValueClass(), 'getValueClass'));
		return call_user_func(array($valueClass, 'getRecordSet'), $this->getID());
	}

	/*####################  Data array transformation ####################*/

	/**
	 *	Returns SpecField array representations
	 *
	 *	@return array
	 */
	public function toArray()
	{
	  	$array = parent::toArray();
	  	$array['fieldName'] = $this->getFormFieldName();
	  	$this->setArrayData($array);
	  	return $array;
	}
}

?>