<?php

namespace eav;

/**
 * Links a pre-defined attribute value to a product
 *
 * @package application/model/specification
 * @author Integry Systems <http://integry.com>
 */
class EavItem extends \ActiveRecordModel implements iEavSpecification
{
	public $valueID;
	public $objectID;
	public $fieldID;
	
	public function initialize()
	{
		$this->belongsTo('valueID', 'eav\EavValue', 'ID', array('alias' => 'Value'));
		$this->belongsTo('objectID', 'eav\EavObject', 'ID', array('alias' => 'Object'));
		$this->belongsTo('fieldID', 'eav\EavField', 'ID', array('alias' => 'Field'));
	}

	public static function getNewInstance(\ActiveRecordModel $owner, EavField $field, EavValue $value)
	{
		$specItem = new self();
		$specItem->set_Value($value);
		$specItem->set_Field($field);
		
		if ($obj = $owner->get_EavObject())
		{
			$specItem->set_Object($obj);
		}

		return $specItem;
	}

	public function setOwner(EavObject $object)
	{
		$this->objectID = $object->getID();
	}
	
	public function setValue(EavValue $value)
	{
		if (!$value->getID())
		{
			return;
		}

		// test whether the value belongs to the same field
		if ($value->get_Field()->getID() != $this->get_Field()->getID())
		{
//			$class = get_class($value->get_Field());
//			throw new Exception('Cannot assign EavValue: ' . $value->get_Field()->getID() . ' value to ' . $class . ':' . $this->get_Field()->getID());
		}

		$this->set_Value($value);
	}
	
	public function getField()
	{
		return $this->get_Field();
	}

	public function getFieldInstance()
	{
		return $this->getField();
	}
	
	protected function _preSaveRelatedRecords()
	{
		return true;
	}

	protected function _postSaveRelatedRecords()
	{
		return true;
	}

	/*
	public function beforeSave()
	{
		if ($this->value && !$this->value)
		{
			return;
		}

		return parent::save($params);
	}
	*/
	
	public function getFormattedValue()
	{
		return $this->get_Value()->value;
	}

	public function toArray()
	{
		if ($value = $this->get_Value())
		{
			return $this->get_Value()->toArray();
		}
	}
	
	public function getRawValue()
	{
		return $this->valueID;
	}
	
	public function replaceValue(EavItem $newValue)
	{
		if ($this->valueID != $newValue->valueID)
		{
			die($newValue->valueID);
		}
		$this->valueID = $newValue->valueID;
	}
}

?>
