<?php

ClassLoader::import('application.model.system.MultilingualObject');

/**
 * Attribute selector value. The same selector value can be assigned to multiple products and usually
 * can be selected from a list of already created values when entering product information - as opposed to
 * input values (numeric or string) that are related to one product only. The advantage of selector values
 * is that they can be used to create product Filters, while input string (SpecificationStringValues) can not.
 *
 * @package application.model.category
 * @author Integry Systems <http://integry.com>
 */
abstract class EavValueCommon extends MultilingualObject
{
	private $mergedFields = array();

	protected abstract function getFieldClass();

	protected function getFieldIDColumnName()
	{
		return call_user_func(array($this->getFieldClass(), 'getFieldIDColumnName'));
	}

	/**
	 * Define SpecFieldValue schema in database
	 */
	public static function defineSchema($className)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARField("position", ARInteger::instance(2)));
		$schema->registerField(new ARField("value", ARArray::instance()));
		$schema->registerField(new ARField("position", ARInteger::instance(2)));

		return $schema;
	}

	/**
	 *  Get new instance of specification field value
	 *
	 *	@param SpecField $field Instance of SpecField (must be a selector field)
	 *  @return SpecFieldValue
	 */
	public static function getNewInstance($className, EavFieldCommon $field)
	{
		if (!in_array($field->type->get(), array(EavFieldCommon::TYPE_NUMBERS_SELECTOR, EavFieldCommon::TYPE_TEXT_SELECTOR)))
		{
			throw new Exception('Cannot create a ' . $className . ' for non-selector field!');
		}

		$instance = parent::getNewInstance($className);
		$instance->getField()->set($field);

		return $instance;
	}

	public static function restoreInstance($className, EavFieldCommon $field, $valueId, $value)
	{
		$instance = self::getNewInstance($className, $field);
		$instance->setID($valueId);
		$instance->value->set(unserialize($value));
		$instance->resetModifiedStatus();

		return $instance;
	}

	/*####################  Value retrieval and manipulation ####################*/

	public function getFormFieldName()
	{
	  	return 'specItem_' . $this->getID();
	}

	public function getField()
	{
		$column = substr($this->getFieldIDColumnName(), 0, -2);
		$column = strtolower(substr($column, 0, 1)) . substr($column, 1);
		return $this->$column;
	}

	/**
	 * Loads a record set of specification field values belonging to specification field
	 *
	 * @param integer $specFieldId
	 * @return ARSet
	 */
	public static function getRecordSet($className, $specFieldId)
	{
		$filter = new ARSelectFilter();
		$filter->setOrder(new ARFieldHandle($className, "position"));
		$fieldColumn = call_user_func(array(call_user_func(array($className, 'getFieldClass')), 'getFieldIDColumnName'));
		$filter->setCondition(new EqualsCond(new ARFieldHandle($className, $fieldColumn), $specFieldId));

		return parent::getRecordSet($className, $filter, false);
	}

	/**
	 * Loads a record set of specification field values belonging to specification field and returns it as array
	 *
	 * @param integer $specFieldId
	 * @return ARSet
	 */
	public static function getRecordSetArray($className, $specFieldId)
	{
		$filter = new ARSelectFilter();
		$filter->setOrder(new ARFieldHandle($className, "position"));
		$fieldColumn = call_user_func(array(call_user_func(array($className, 'getFieldClass')), 'getFieldIDColumnName'));
		$filter->setCondition(new EqualsCond(new ARFieldHandle($className, $fieldColumn), $specFieldId));

		return parent::getRecordSetArray($className, $filter, false);
	}

	/*####################  Saving ####################*/

	public function mergeWith(EavValueCommon $specFieldValue)
	{
		if(!$specFieldValue->isExistingRecord())
		{
			throw new ApplicationException(get_class($specFieldValue) . ' should be an existing record');
		}

		if (get_class($this) != get_class($specFieldValue))
		{
			throw new ApplicationException('Value classes do not match');
		}

		if ($this === $specFieldValue)
		{
			return;
		}

		if (!in_array($specFieldValue, $this->mergedFields))
		{
			$this->mergedFields[] = $specFieldValue;
		}
	}
	
	protected static function getRelativeImagePath($className, $id)
	{
		return 'upload/attrImage/' . substr(lcfirst($className), 0, -5) . '/' . $id . '.jpg';
	}
	
	public function getImagePath($isFullPath = false)
	{
		$path = self::getRelativeImagePath(get_class($this), $this->getID());
		
		return $isFullPath ? ClassLoader::getRealPath('public.') . $path : $path;
	}

	public function save($forceOperation = false)
	{
		parent::save($forceOperation);
		$this->mergeFields();
	}

	protected function insert()
	{
	   	// get current max position
		if (!$this->position->get())
		{
			$filter = new ARSelectFilter();
		   	$cond = new EqualsCond(new ARFieldHandle(get_class($this), $this->getFieldIDColumnName()), $this->getField()->get()->getID());
		   	$filter->setCondition($cond);
			$filter->setOrder(new ARFieldHandle(get_class($this), 'position'), 'DESC');
		   	$filter->setLimit(1);
		   	$res = ActiveRecordModel::getRecordSet(get_class($this), $filter);
		   	if ($res->size() > 0)
		   	{
			 	$item = $res->get(0);
				$pos = $item->position->get() + 1;
			}
			else
			{
				$pos = 0;
			}

			$this->position->set($pos);
		}

		return parent::insert();
	}

	/**
	 *	@todo Rewrite this to use ARUpdateFilter or simply an SQL query to update all values

		As in the original bug report:

			The update query for merging value 799 into 808 would look something like this:

			UPDATE SpecificationItem
			LEFT JOIN SpecificationItem AS SecondItem ON SpecificationItem.productID=SecondItem.productID AND SecondItem.specFieldValueID=808
			SET SpecificationItem.specFieldValueID=808
			WHERE SpecificationItem.specFieldValueID=799 AND SecondItem.specFieldValueID IS NULL

			- it would only update products that do not have value 808 already set (otherwise the query would error because of duplicate SpecificationItem records).

			The remaining records with value 799 will be automatically removed (cascade) when SpecFieldValue 799 is deleted.
	 */
	private function mergeFields()
	{
		if(empty($this->mergedFields)) return true;

		$itemClass = call_user_func(array($this->getFieldClass(), 'getSelectValueClass'));
		$valueColumn = call_user_func(array($itemClass, 'getValueIDColumnName'));

		$db = ActiveRecord::getDBConnection();
		$specificationItemSchema = self::getSchemaInstance($itemClass);
		$foreignKeys = $specificationItemSchema->getForeignKeyList();
		$specFieldReferenceFieldName = '';
		foreach($foreignKeys as $foreignKey)
		{
			if($foreignKey->getForeignClassName() == get_class($this))
			{
				$specFieldReferenceFieldName = $foreignKey->getName();
				break;
			}
		}

		$thisSchema = self::getSchemaInstance(get_class($this));
		$primaryKeyList = $thisSchema->getPrimaryKeyList();
		$promaryKey = array_shift($primaryKeyList);

		$mergedFieldsIDs = array();
		foreach($this->mergedFields as $mergedField)
		{
			$mergedFieldsIDs[] = $mergedField->getID();
		}
		$inAllItemsExceptThisCondition = new INCond(new ARFieldHandle(get_class($this), $promaryKey->getName()), $mergedFieldsIDs);

		$mergedFieldsIDs[] = $this->getID();
		$inAllItemsCondition = new INCond(new ARFieldHandle($itemClass, $specFieldReferenceFieldName), $mergedFieldsIDs);

		// Create filters
		$mergedSpecificationItemsFilter = new ARSelectFilter();
		$mergedSpecificationItemsFilter->setCondition($inAllItemsCondition);

		// Using IGNORE I'm ignoring duplicate primary keys. Those rows that violate the uniqueness of the primary key are simply not saved
		// Then later I just delete these records and the merge is complete.
		$sql = 'UPDATE IGNORE ' . $itemClass . ' SET ' . $valueColumn . ' = ' . $this->getID() . ' ' . $mergedSpecificationItemsFilter->createString();
		self::getLogger()->logQuery($sql);
		$db->executeUpdate($sql);

		$mergedSpecFieldValuesDeleteFilter = new ARDeleteFilter();
		$mergedSpecFieldValuesDeleteFilter->setCondition($inAllItemsExceptThisCondition);
		ActiveRecord::deleteRecordSet(get_class($this), $mergedSpecFieldValuesDeleteFilter);
	}
	
	/*####################  Data array transformation ####################*/

	public static function transformArray($array, ARSchema $schema)
	{
		$array = parent::transformArray($array, $schema);
		$path = self::getRelativeImagePath($schema->getName(), $array['ID']);

		if (file_exists($path))
		{
			$array['imagePath'] = $path;
		}

		return $array;
	}

	/**
	 *	Returns array representations
	 *
	 *	@return array
	 */
	public function toArray()
	{
	  	$array = parent::toArray();
	  	
	  	$imagePath = $this->getImagePath();
	  	
	  	if (file_exists($imagePath))
	  	{
			$array['imagePath'] = $imagePath;
		}
	  	
	  	$this->setArrayData($array);
	  	return $array;
	}
}

?>
