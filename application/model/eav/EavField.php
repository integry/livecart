<?php

namespace eav;

use eavcommon\EavFieldCommon;

/**
 * Specification attributes allow to define specific product models with a specific set of features or parameters.
 *
 * Each SpecField is a separate attribute. For example, screen size for laptops, ISBN code for books,
 * horsepowers for cars, etc. Since SpecFields are linked to categories, products from different categories can
 * have different set of attributes.
 *
 * @package application/model/eav
 * @author Integry Systems <http://integry.com>
 */
class EavField extends EavFieldCommon
{
	private static $eavClasses = null;
	private $values = array();

	public $ID;
	public $classID;
	public $stringIdentifier;

//	$group = $className . 'Group';
//	$schema->registerField(new ARForeignKeyField(strtolower(substr($group, 0, 1)) . substr($group, 1) . 'ID', $group, "ID", $group, ARInteger::instance()));
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

	public function initialize()
	{
        $this->hasMany('ID', 'eav\EavValue', 'fieldID', array(
            'alias' => 'EavValue',
            'foreignKey' => array(
                'action' => \Phalcon\Mvc\Model\Relation::ACTION_CASCADE
            )
        ));

        $this->hasMany('ID', 'eav\EavObjectValue', 'fieldID', array(
            'alias' => 'EavObjectValue',
            'foreignKey' => array(
                'action' => \Phalcon\Mvc\Model\Relation::ACTION_CASCADE
            )
        ));

        $this->hasMany('ID', 'eav\EavItem', 'fieldID', array(
            'alias' => 'EavItem',
            'foreignKey' => array(
                'action' => \Phalcon\Mvc\Model\Relation::ACTION_CASCADE
            )
        ));
	}

	const DATATYPE_TEXT = 1;
	const DATATYPE_NUMBERS = 2;

	const TYPE_NUMBERS_SELECTOR = 1;
	const TYPE_NUMBERS_SIMPLE = 2;

	const TYPE_TEXT_SIMPLE = 3;
	const TYPE_TEXT_ADVANCED = 4;
	const TYPE_TEXT_SELECTOR = 5;
	const TYPE_TEXT_DATE = 6;
	
	public function getLangFields()
	{
		return array('name');
	}

	public static function getClassID($className)
	{
		if ($className instanceof EavObject)
		{
			return $className->classID;
		}

		if (is_object($className))
		{
			$className = get_class($className);
		}
		
		$className = explode('\\', $className);
		$className = array_pop($className);

		$classes = self::getEavClasses();
		if (isset($classes[$className]))
		{
			return $classes[$className];
		}
		else
		{
			return 0;
			//throw new ApplicationException($className . ' is not a valid EAV class');
		}
	}

	public function getClassNameById($id)
	{
		return array_search($id, self::getEavClasses());
	}

	public static function getEavClasses()
	{
		if (!self::$eavClasses)
		{
			self::$eavClasses = array(
					'Product'=> 1,
					'CustomerOrder'=> 2,
					'User' => 4,
					'UserAddress' => 5,
					'Manufacturer' => 3,
					'Category' => 8,
					'UserGroup' => 5,
					'Transaction' => 0,
					'ShippingService' => 6,
					'StaticPage' => 7
				);
		}

		return self::$eavClasses;
	}

	public static function registerClass($className, $id)
	{
		self::getEavClasses();
		self::$eavClasses[$className] = $id;
	}

	public function getOwnerClass()
	{
		return 'eav\EavObject';
	}

	public function getStringValueClass()
	{
		return 'eav\EavObjectValue';
	}

	public function getNumericValueClass()
	{
		return 'eav\EavObjectValue';
	}

	public function getDateValueClass()
	{
		return 'eav\EavObjectValue';
	}

	public function getSelectValueClass()
	{
		return 'eav\EavItem';
	}

	public function getMultiSelectValueClass()
	{
		return 'eav\EavMultiValueItem';
	}

	public function getFieldIDColumnName()
	{
		return 'fieldID';
	}

	public function getObjectIDColumnName()
	{
		return 'objectID';
	}

	public function getOwnerIDColumnName()
	{
		return 'classID';
	}

	protected function getParentCondition()
	{
		//return 'classID = :classID:', array('classID' => $this->classID);
	}
	
	public function registerValue(EavValue $value)
	{
		$this->values[$value->getID()] = $value;
	}
	
	public function getValue($id)
	{
		return $this->values[$id];
	}

	public function toJson($encode = true)
	{
		$array = $this->toArray();
		if ($this->values)
		{
			foreach ($this->values as $value)
			{
				$array['values'][] = $value->toArray();
			}
		}
		
		if ($encode)
		{
			return htmlspecialchars(json_encode($array));
		}
		else
		{
			return $array;
		}
	}

	/*####################  Static method implementations ####################*/

	/**
	 * Get a new EavFieldCommon instance
	 *
	 * @param string $className Instance class name
	 * @param int $dataType Data type code (ex: self::DATATYPE_TEXT)
	 * @param int $type Field type code (ex: self::TYPE_TEXT_SIMPLE)
	 *
	 * @return  EavFieldCommon
	 */
	public static function getNewInstance($dataType = false, $type = false)
	{
/*
		if ($className instanceof EavFieldManager)
		{
			$className = $className->getClassName();
		}

		$field = parent::getNewInstance(__CLASS__, $dataType, $type);
		$field->classID = self::getClassID($className);
*/
		$field = new EavField();

		if ($dataType)
		{
			$field->dataType = $dataType;
			$field->type = $type;
		}

		return $field;
	}

	public static function getFieldsByClass($className)
	{
		$f = query::query()->where('EavField.classID = :EavField.classID:', array('EavField.classID' => EavField::getClassID($className)));
		$f->orderBy('EavField.position');

		return self::getRecordSet('EavField', $f);
	}

	/**
	 * Adds a 'choice' value to this field
	 *
	 * @param SpecFieldValue $value
	 */
	public function addValue(EavValue $value)
	{
		$value->fieldID = $this->getID();
		$value->save();
	}
	
	public static function getGroupIDColumnName($className)
	{
		$group = $className . 'Group';
		return strtolower(substr($group, 0, 1)) . substr($group, 1) . 'ID';
	}

	public function getNewValueInstance()
	{
		$class = call_user_func(array($this->getSelectValueClass(), 'getValueClass'));
		return call_user_func_array(array($class, 'getNewInstance'), array($this));
	}

	public function getValueInstanceByID($id)
	{
		foreach ($this->values as $value)
		{
			if ($value->getID() == $id)
			{
				return $value;
			}
		}
	}
	
	public function getRegisteredValues()
	{
		return $this->values;
	}

	/*####################  Value retrieval and manipulation ####################*/

	/**
	 * Gets a related table name, where field values are stored
	 *
	 * @return array
	 */
	public function getValueTableName()
	{
		switch ($this->type)
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
				throw new Exception('Invalid field type: ' . $this->type);
		}
	}

	public function getObjectValueField()
	{
		switch ($this->type)
		{
			case EavField::TYPE_NUMBERS_SIMPLE:
				$valueField = 'numValue';
			break;

			case EavField::TYPE_TEXT_SIMPLE:
				$valueField = 'stringValue';
			break;

			case EavField::TYPE_TEXT_ADVANCED:
				$valueField = 'textValue';
			break;

			case EavField::TYPE_TEXT_DATE:
				$valueField = 'dateValue';
			break;
		}
		
		return $valueField;
	}

	public function getSpecificationFieldClass()
	{
		$specValueClass = $this->getValueTableName();
		if ($this->getSelectValueClass() == $specValueClass)
		{
			if ($this->isMultiValue)
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
		return in_array($this->type, self::getSelectorValueTypes());
	}

	/**
	 * Check if current specification field is text type
	 *
	 * @return boolean
	 */
	public function isTextField()
	{
		return in_array($this->type, array(self::TYPE_TEXT_SIMPLE, self::TYPE_TEXT_ADVANCED));
	}

	/**
	 * Check if current specification field type is simple numbers
	 *
	 * @return boolean
	 */
	public function isSimpleNumbers()
	{
		return $this->type == self::TYPE_NUMBERS_SIMPLE;
	}

	public function isNumeric()
	{
		return $this->dataType == self::DATATYPE_NUMBERS;
	}

	/**
	 * Check if current specification field type is date
	 *
	 * @return boolean
	 */
	public function isDate()
	{
		return $this->type == self::TYPE_TEXT_DATE;
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
		return 'eav_' . $this->getID();
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
	public function defineJoin($query, $ownerTable = null)
	{
		$ownerTable = $ownerTable ? $ownerTable . '.' : '';
		
		$table = $this->getJoinAlias();
		$query->join($this->getValueTableName(), $table . '.fieldID=' . $this->getID()  . ' AND ' . $ownerTable . 'eavObjectID=' . $table . '.objectID', $table, 'LEFT');
		//$query->columns($table . '.*');

		if ($this->isSelector() && !$this->isMultiValue)
		{
			$query->join('eav\EavValue', $table . '_value.ID = ' . $table . '.valueID', $table . '_value', 'LEFT');
			//$query->columns($table . '_value.*');
			
			/*
			$itemClass = $this->getSelectValueClass();
			$valueClass = call_user_func(array($itemClass, 'getValueClass'));
			$valueField = call_user_func(array($itemClass, 'getValueIDColumnName'));
			$filter->joinTable($valueClass, $table, 'ID', $valueField, $table . '_value');
			*/
		}
	}

	/*####################  Saving ####################*/

	public function beforeCreate()
	{
		parent::beforeCreate();
		$this->setLastPosition();
	}

	/**
	 * Loads a set of spec field records in current category
	 *
	 * @return ARSet
	 */
	public function getValues()
	{
		return EavValue::query()->where('fieldID = :field:', array('field' => $this->getID()))->orderBy('position')->execute();
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
	  	return $array;
	}
}

?>
