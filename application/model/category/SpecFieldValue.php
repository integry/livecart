<?php

ClassLoader::import("application.model.system.MultilingualObject");

/**
 * Specification field value class
 *
 * @package application.model.category
 */
class SpecFieldValue extends MultilingualObject
{
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

	/**
	 * Delete value from database
	 * 
	 * @param integer $id Specifiaction field value's id
	 */
	public static function deleteById($id)
	{
	    parent::deleteByID(__CLASS__, (int)$id);
	}
	
	public function getFormFieldName()
	{
	  	return 'specItem_' . $this->getID();
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
}

?>