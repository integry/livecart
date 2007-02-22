<?php

ClassLoader::import("application.model.specification.SpecificationItem");

/**
 * Product specification wrapper class
 * Loads/modifies product specification data
 *
 * @author Saulius Rupainis <saulius@integry.net>
 * @package application.model.product
 */
class ProductSpecification
{
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
	 * @param Specification $specification Specification item value
	 */
	public function setAttribute(iSpecification $specification)
	{
		$this->attributes[$specification->getSpecField()->getID()] = $specification;
		unset($this->removedAttributes[$specification->getSpecField()->getID()]);
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
	  	if (!$field->isMultiValue->get())
	  	{
		    throw new Exception('Cannot remove a value from non-multivalue select field');
		}
		
		if (!isset($this->attributes[$field->getID()]))
		{
		  	return false;
		}
		
		$this->attributes[$field->getID()]->removeValue($value);		
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
	 */
	public function getAttribute(SpecField $field, $defaultValue = null)
	{
		if (!$this->isAttributeSet($field))
		{
		  	$this->attributes[$field->getID()] = $field->getNewSpecificationInstance($this->product, $defaultValue);
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
		 	$arr[$id] = $attribute->toArray(ActiveRecordModel::NON_RECURSIVE);		 	
		}

		return $arr;
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
			$specification = call_user_func_array(array($class, 'restoreInstance'), array($class, $this->product, $specField, $value['value']));
			
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

/*
	echo '<pre>';
	print_r($this->toArray());
	echo '</pre>';
*/
	}

}

?>