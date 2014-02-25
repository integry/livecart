<?php

namespace eav;

/**
 * An attribute value that is assigned to a particular product.
 *
 * @package application/model/specification
 * @author Integry Systems <http://integry.com>
 */
class EavObjectValue extends \ActiveRecordModel implements iEavSpecification
{
	public $ID;
	public $objectID;
	public $fieldID;
	public $numValue;
	public $stringValue;
	public $textValue;
	public $dateValue;
	protected $manager = null;

	public function setManager(EavSpecificationManager $manager)
	{
		$this->manager = $manager;
	}

	public function initialize()
	{
		$this->belongsTo('objectID', 'eav\EavObject', 'ID', array('alias' => 'EavObject'));
		$this->belongsTo('fieldID', 'eav\EavField', 'ID', array('alias' => 'EavField'));
	}
	
	protected function _postSaveRelatedRecords()
	{
		return true;
	}
	
	public function save($data = NULL, $whiteList = NULL)
	{
		$trans = $this->getDI()->get('transactionManager');
		parent::save($data, $whiteList);
		$trans->setRollbackPendent(false);
		return true;
	}
	
	protected function _preSaveRelatedRecords()
	{
		return true;
	}

	public function getField()
	{
		return $this->getEavField();
	}
	
	public function getFieldInstance()
	{
		return $this->getField();
	}

	public function setOwner(EavObject $eavObject)
	{
		$this->objectID = $eavObject->getID();
	}

	public static function getNewInstance(\ActiveRecordModel $owner, EavField $field, $value)
	{
		$specItem = new self();
		
		if ($obj = $owner->get_EavObject())
		{
			$specItem->set_Object($obj);
		}
		
		$specItem->set_Field($field);
		
		$valueField = $specItem->getValueField();
		$specItem->$valueField = $value;

		return $specItem;
	}
	
	public function getValueField()
	{
		return $this->getField()->getObjectValueField();
	}
	
	public function setValue($value)
	{
		$valueField = $this->getValueField();
		
		if ($valueField == 'dateValue')
		{
			if (preg_match('/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}T/', $value))
			{
				$value = substr($value, 0, 10);
			}
			
			$value = date('Y-m-d', strtotime($value));
		}
		
		$this->$valueField = $value;
	}

	/*
	public static function restoreInstance($className, ActiveRecordModel $owner, EavFieldCommon $field, $value)
	{
		$specItem = parent::getInstanceByID($className, array(call_user_func(array($className, 'getOwnerIDColumnName')) => $owner->getID(),
														  call_user_func(array($className, 'getFieldIDColumnName')) => $field->getID()));
		$specItem->value->set($value);
		$specItem->resetModifiedStatus();

		return $specItem;
	}
	*/

/*
	public function getValueByLang($fieldName, $langCode = null, $returnDefaultIfEmpty = true)
	{
		return MultiLingualObject::getValueByLang($fieldName, $langCode, $returnDefaultIfEmpty);
	}

	public function setValueByLang($langCode, $value)
	{
		$currentValue = $this->value;
		if (!is_array($currentValue))
		{
		  	$currentValue = array();
		}

		$currentValue[$langCode] = $value;
		$this->value->set($currentValue);
	}
*/

	public function getValue()
	{
		$field = $this->getValueField();
		return $this->$field;
	}
	
	public function getRawValue()
	{
		return $this->getValue();
	}
	
	public function getFormattedValue($options = array())
	{
		$value = $this->getRawValue();
		
		if (!empty($options['format']))
		{
			$value = date($options['format'], strtotime($value));
		}
		
		return $value;
	}
	
	public function replaceValue(EavObjectValue $newValue)
	{
		$field = $this->getValueField();
		$this->$field = $newValue->$field;
	}
}

?>
