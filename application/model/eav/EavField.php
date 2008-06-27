<?php

ClassLoader::import('application.model.eavcommon.EavFieldCommon');
ClassLoader::import('application.model.eav.EavObject');
ClassLoader::import('application.model.eav.EavValue');
ClassLoader::import('application.model.eav.EavFieldGroup');
ClassLoader::import('application.model.eav.*');

/**
 * Specification attributes allow to define specific product models with a specific set of features or parameters.
 *
 * Each SpecField is a separate attribute. For example, screen size for laptops, ISBN code for books,
 * horsepowers for cars, etc. Since SpecFields are linked to categories, products from different categories can
 * have different set of attributes.
 *
 * @package application.model.eav
 * @author Integry Systems <http://integry.com>
 */
class EavField extends EavFieldCommon
{
	/**
	 * Define database schema
	 */
	public static function defineSchema($className = __CLASS__)
	{
		$schema = parent::defineSchema($className);
		$schema->registerField(new ARField("classID", ARInteger::instance()));
	}

	public function getClassID($className)
	{
		$classes = self::getEavClasses();
		if (isset($classes[$className]))
		{
			return $classes[$className];
		}
		else
		{
			throw new ApplicationException($className . ' is not a valid EAV class');
		}
	}

	public function getClassNameById($id)
	{
		return array_search($id, self::getEavClasses());
	}

	public function getEavClasses()
	{
		return array(
					'Category' => 1,
					'CustomerOrder'=> 2,
					'Manufacturer' => 3,
					'User' => 4,
					'UserGroup' => 5,
				);
	}

	public function getOwnerClass()
	{
		return 'EavObject';
	}

	public function getStringValueClass()
	{
		return 'EavStringValue';
	}

	public function getNumericValueClass()
	{
		return 'EavNumericValue';
	}

	public function getDateValueClass()
	{
		return 'EavDateValue';
	}

	public function getSelectValueClass()
	{
		return 'EavItem';
	}

	public function getMultiSelectValueClass()
	{
		return 'EavMultiValueItem';
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
		return new EqualsCond(new ARFieldHandle(get_class($this), 'classID'), $this->classID->get());
	}

	/*####################  Static method implementations ####################*/

	/**
	 * Get instance record by id
	 *
	 * @param mixred $recordID Id
	 * @param bool $loadRecordData If true load data
	 * @param bool $loadReferencedRecords If true load references. And $loadRecordData is true load a data also
	 *
	 * @return  EavField
	 */
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false)
	{
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords);
	}

	/**
	 * Get a new SpecField instance
	 *
	 * @param int Class ID
	 * @param int $dataType Data type code (ex: self::DATATYPE_TEXT)
	 * @param int $type Field type code (ex: self::TYPE_TEXT_SIMPLE)
	 *
	 * @return EavField
	 */
	public static function getNewInstance($className, $dataType = false, $type = false)
	{
		if ($className instanceof EavFieldManager)
		{
			$className = $className->getClassName();
		}

		$field = parent::getNewInstance(__CLASS__, $dataType, $type);
		$field->classID->set(self::getClassID($className));

		return $field;
	}

	public static function getFieldsByClass($className)
	{
		$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('EavField', 'classID'), EavField::getClassID($className)));
		$f->setOrder(new ARFieldHandle('EavField', 'position'));

		return self::getRecordSet('EavField', $f);
	}

	/*####################  Value retrieval and manipulation ####################*/

	/**
	 * Adds a 'choice' value to this field
	 */
	public function addValue(EavValue $value)
	{
		return parent::addValue($value);
	}
}

?>