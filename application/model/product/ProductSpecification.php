<?php

ClassLoader::import("application.model.specification.SpecificationItem");

/**
 * Product specification wrapper class
 * Loads/modifies product specification data
 *
 * @author Integry Systems
 * @package application.model.product
 */
class ProductSpecification
{
	/**
	 * Product instance
	 *
	 * @var Product
	 */
	private $product = null;
	
	private $attributes = array();
	
	private $removedAttributes = array();

	public function __construct(Product $product, $specificationDataArray = array())
	{
		$this->product = $product;
		$this->loadSpecificationData($specificationDataArray);
	}

	/**
	 * Sets specification attribute value by mapping product, specification field, and
	 * assigned value to one record (atomic item)
	 *
	 * @param iSpecification $specification Specification item value
	 */
	public function setAttribute(iSpecification $newSpecification)
	{		
		$specField = $newSpecification->getSpecField();

		if(
			$this->product->isExistingRecord()   
			&& isset($this->attributes[$newSpecification->getSpecField()->getID()]) 
			&& ('SpecificationItem' == $specField->getSpecificationFieldClass() 
			&& $newSpecification->specFieldValue->isModified())
		)
		{
			// Delete old value
			ActiveRecord::deleteByID('SpecificationItem', $this->attributes[$specField->getID()]->getID());
			
			// And create new
			$this->attributes[$specField->getID()] = SpecificationItem::getNewInstance($this->product, $specField, $newSpecification->specFieldValue->get());
		}
		else
		{
			$this->attributes[$specField->getID()] = $newSpecification;
		}

		unset($this->removedAttributes[$specField->getID()]);
	}

	/**
	 * Removes persisted product specification property
	 *
	 *	@param SpecField $field SpecField instance
	 */
	public function removeAttribute(SpecField $field)
	{
		
		$this->removedAttributes[$field->getID()] = $this->attributes[$field->getID()];
		unset($this->attributes[$field->getID()]);
	}

	public function removeAttributeValue(SpecField $field, SpecFieldValue $value)
	{
	  	
		if (!$field->isSelector())
	  	{
		    throw new Exception('Cannot remove a value from non selector type specification field');
		}
		
		if (!isset($this->attributes[$field->getID()]))
		{
		  	return false;
		}
		
		if ($field->isMultiValue->get())
		{
			$this->attributes[$field->getID()]->removeValue($value);		
		}
		else
		{
			// no changes should be made until the save() function is called
			$this->attributes[$field->getID()]->delete();
		}
	}

	public function isAttributeSet(SpecField $field)
	{
		
		return isset($this->attributes[$field->getID()]);  
	}
	
	/**
	 *	Get attribute instance for the particular SpecField.
	 *	
	 *	If it is a single value selector a SpecFieldValue instance needs to be passed as well
	 *
	 *	@param SpecField $field SpecField instance
	 *	@param SpecFieldValue $defaultValue SpecFieldValue instance (or nothing if SpecField is not selector)
	 *
	 * @return Specification
	 */
	public function getAttribute(SpecField $field, $defaultValue = null)
	{
		if (!$this->isAttributeSet($field))
		{
		  	$params = array($this->product, $field, $defaultValue);
			$this->attributes[$field->getID()] = call_user_func_array(array($field->getSpecificationFieldClass(), 'getNewInstance'), $params);
		}


		return $this->attributes[$field->getID()];  	
	}

	public function save()
	{
		
		foreach ($this->removedAttributes as $attribute)
		{
		  	$attribute->delete();
		}  
		$this->removedAttributes = array();

		foreach ($this->attributes as $attribute)
		{
			$attribute->save();
		}  
	}

	public function toArray()
	{
		$arr = array();
		foreach ($this->attributes as $id => $attribute)
		{
            $arr[$id] = $attribute->toArray();		 	
		}

		return $arr;
	}

	public static function loadSpecificationForRecordSetArray(&$productArray)
	{
	  	$ids = array();
		foreach ($productArray as $key => $product)
	  	{
			$ids[$product['ID']] = $key;
		}
		
		$cond = '
		LEFT JOIN 	
			SpecField ON specFieldID = SpecField.ID 
		LEFT JOIN 	
			SpecFieldGroup ON SpecField.specFieldGroupID = SpecFieldGroup.ID 
		WHERE 
			productID IN (' . implode(', ', array_flip($ids)) . ') AND SpecField.isDisplayedInList = 1';

	    $query = '
		SELECT SpecificationDateValue.*, NULL AS specFieldValueID, NULL AS specFieldValuePosition, SpecFieldGroup.position AS SpecFieldGroupPosition, SpecField.* as valueID FROM SpecificationDateValue ' . $cond . '
	    UNION
		SELECT SpecificationStringValue.*, NULL, NULL AS specFieldValuePosition, SpecFieldGroup.position, SpecField.* as valueID FROM SpecificationStringValue ' . $cond . '
	    UNION
		SELECT SpecificationNumericValue.*, NULL, NULL AS specFieldValuePosition, SpecFieldGroup.position, SpecField.* as valueID FROM SpecificationNumericValue ' . $cond . '
	    UNION
		SELECT SpecificationItem.productID, SpecificationItem.specFieldID, SpecFieldValue.value, SpecFieldValue.position, SpecFieldGroup.position, SpecFieldValue.ID, SpecField.*
				 FROM SpecificationItem
				 	LEFT JOIN SpecFieldValue ON SpecificationItem.specFieldValueID =  SpecFieldValue.ID
				 ' . str_replace('ON specFieldID', 'ON SpecificationItem.specFieldID', $cond) . 
                 ' ORDER BY productID, SpecFieldGroupPosition, position, specFieldValuePosition';
		
		$specificationArray = ActiveRecordModel::getDataBySQL($query);
		$multiLingualFields = array('name', 'description', 'valuePrefix', 'valueSuffix');

		foreach ($specificationArray as &$spec)
		{
			// transform array for presentation
			foreach ($multiLingualFields as $value)
			{
				$spec[$value] = unserialize($spec[$value]);
			}
			
			if (SpecField::DATATYPE_TEXT == $spec['dataType'] && SpecField::TYPE_TEXT_DATE != $spec['type'])
			{
                $spec['value'] = unserialize($spec['value']);
			}

			$spec = MultiLingualObject::transformArray($spec, 'SpecificationStringValue');
			$spec = MultiLingualObject::transformArray($spec, 'SpecField');

			// append to product array
			$productArray[$ids[$spec['productID']]]['attributes'][$spec['specFieldID']] = $spec;			
		}
	}

	private function loadSpecificationData($specificationDataArray)
	{	
		// preload all specFields from database
		$specFieldIds = array();

		$selectors = array();
		$simpleValues = array();
		foreach ($specificationDataArray as $value)
		{
		  	$specFieldIds[$value['specFieldID']] = $value['specFieldID'];
		  	if ($value['valueID'])
		  	{
		  		$selectors[$value['specFieldID']][$value['valueID']] = $value;	    
			}
			else
			{
				$simpleValues[$value['specFieldID']] = $value;  
			}
		}

		$specFields = ActiveRecordModel::getInstanceArray('SpecField', $specFieldIds);

		// simple values
		foreach ($simpleValues as $value)
		{
		  	$specField = $specFields[$value['specFieldID']];

		  	$class = $specField->getValueTableName();	
		  	
			$specification = call_user_func_array(array($class, 'restoreInstance'), array($this->product, $specField, $value['value']));
		  	$this->attributes[$specField->getID()] = $specification;
		}		  

		// selectors
		foreach ($selectors as $specFieldId => $value)
		{
			$specField = $specFields[$specFieldId];
		  	if ($specField->isMultiValue->get())
		  	{
				$values = array();
				foreach ($value as $val)
				{
					$values[$val['valueID']] = $val['value'];
				}
				$specification = MultiValueSpecificationItem::restoreInstance($this->product, $specField, $values);
			}
			else
			{
			  	$value = array_pop($value);
				$specFieldValue = SpecFieldValue::restoreInstance($specField, $value['valueID'], $value['value']);
				$specification = SpecificationItem::restoreInstance($this->product, $specField, $specFieldValue);
			}

		  	$this->attributes[$specField->getID()] = $specification;
		}
	}

}

?>