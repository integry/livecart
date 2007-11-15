<?php

ClassLoader::import("application.model.system.MultilingualObject");

/**
 * Attribute selector value. The same selector value can be assigned to multiple products and usually
 * can be selected from a list of already created values when entering product information - as opposed to
 * input values (numeric or string) that are related to one product only. The advantage of selector values
 * is that they can be used to create product Filters, while input string (SpecificationStringValues) can not.
 *
 * @package application.model.category
 * @author Integry Systems <http://integry.com> 
 */
class SpecFieldValue extends MultilingualObject
{
	private $mergedFields = array();	
	
	/**
	 * Define SpecFieldValue schema in database
	 */
	public static function defineSchema()
	{
		$schema = self::getSchemaInstance(__CLASS__);
		$schema->setName("SpecFieldValue");

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("specFieldID", "SpecField", "ID", "SpecField", ARInteger::instance()));
		$schema->registerField(new ARField("position", ARInteger::instance(2)));
		$schema->registerField(new ARField("value", ARArray::instance()));
		$schema->registerField(new ARField("position", ARInteger::instance(2)));
	}

	/*####################  Static method implementations ####################*/

	/**
	 *  Get new instance of specification field value
	 *
	 *	@param SpecField $field Instance of SpecField (must be a selector field)
	 *  @return SpecFieldValue
	 */
	public static function getNewInstance(SpecField $field)
	{
		if (!in_array($field->type->get(), array(SpecField::TYPE_NUMBERS_SELECTOR, SpecField::TYPE_TEXT_SELECTOR)))
		{
			throw new Exception('Cannot create a SpecFieldValue for non-selector field!');
		}
		
		$instance = parent::getNewInstance(__CLASS__);
		$instance->specField->set($field);
		
		return $instance;
	}

	/**
	 * Get active record instance
	 *
	 * @param integer $recordID
	 * @param boolean $loadRecordData
	 * @param boolean $loadReferencedRecords
	 * @return SpecFieldValue
	 */
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false)
	{
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords);
	}
	
	/**
	 * Loads a record set of specification field values belonging to specification field
	 *
	 * @param integer $specFieldId
	 * @return ARSet
	 */
	public static function getRecordSet($specFieldId)
	{
		$filter = new ARSelectFilter();
		$filter->setOrder(new ARFieldHandle(__CLASS__, "position"));
		$filter->setCondition(new EqualsCond(new ARFieldHandle(__CLASS__, 'specFieldID'), $specFieldId));

		return parent::getRecordSet(__CLASS__, $filter, false);
	}

	/**
	 * Loads a record set of specification field values belonging to specification field and returns it as array
	 *
	 * @param integer $specFieldId
	 * @return ARSet
	 */
	public static function getRecordSetArray($specFieldId)
	{
		$filter = new ARSelectFilter();
		$filter->setOrder(new ARFieldHandle(__CLASS__, "position"));
		$filter->setCondition(new EqualsCond(new ARFieldHandle(__CLASS__, 'specFieldID'), $specFieldId));

		return parent::getRecordSetArray(__CLASS__, $filter, false);
	}

	public static function restoreInstance(SpecField $field, $valueId, $value)
	{		
		$instance = self::getNewInstance($field);
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

	public function mergeWith(SpecFieldValue $specFieldValue)
	{
		if(!$specFieldValue->isExistingRecord()) 
		{
			throw new ApplicationException('SpecFieldValue should be an existing record');			
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
	
	/*####################  Saving ####################*/	
	
	/**
	 * Delete value from database
	 * 
	 * @param integer $id Specifiaction field value's id
	 */
	public static function deleteById($id)
	{
		parent::deleteByID(__CLASS__, (int)$id);
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
		   	$cond = new EqualsCond(new ARFieldHandle('SpecFieldValue', 'specFieldID'), $this->specField->get()->getID());
		   	$filter->setCondition($cond);
			$filter->setOrder(new ARFieldHandle('SpecFieldValue', 'position'), 'DESC');
		   	$filter->setLimit(1);
		   	$res = ActiveRecordModel::getRecordSet('SpecFieldValue', $filter);
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
		
		$db = ActiveRecord::getDBConnection();
		$specificationItemSchema = self::getSchemaInstance('SpecificationItem');
		$foreignKeys = $specificationItemSchema->getForeignKeyList();
		$specFieldReferenceFieldName = '';
		foreach($foreignKeys as $foreignKey)
		{
			if($foreignKey->getForeignClassName() == __CLASS__)
			{
				$specFieldReferenceFieldName = $foreignKey->getName();
				break;
			}
		}
		
		$thisSchema = self::getSchemaInstance(__CLASS__);
		$primaryKeyList = $thisSchema->getPrimaryKeyList();
		$promaryKey = array_shift($primaryKeyList);
		
		$mergedFieldsIDs = array();
		foreach($this->mergedFields as $mergedField) $mergedFieldsIDs[] = $mergedField->getID();
		$inAllItemsExceptThisCondition = new INCond(new ARFieldHandle(__CLASS__, $promaryKey->getName()), $mergedFieldsIDs);
		
		$mergedFieldsIDs[] = $this->getID();
		$inAllItemsCondition = new INCond(new ARFieldHandle('SpecificationItem', $specFieldReferenceFieldName),$mergedFieldsIDs);
		
		// Create filters
		$mergedSpecificationItemsFilter = new ARSelectFilter();
		$mergedSpecificationItemsFilter->setCondition($inAllItemsCondition);	

		// Using IGNORE I'm ignoring duplicate primary keys. Those rows that violate the uniqueness of the primary key are simply not saved
		// Then later I just delete these records and the merge is complete. 
		$sql = "UPDATE IGNORE SpecificationItem SET specFieldValueID = " . $this->getID() . " " . $mergedSpecificationItemsFilter->createString();
		self::getLogger()->logQuery($sql);
		$db->executeUpdate($sql);
	
		$mergedSpecFieldValuesDeleteFilter = new ARDeleteFilter();
		$mergedSpecFieldValuesDeleteFilter->setCondition($inAllItemsExceptThisCondition);
		ActiveRecord::deleteRecordSet('SpecFieldValue', $mergedSpecFieldValuesDeleteFilter);
	}
}
?>