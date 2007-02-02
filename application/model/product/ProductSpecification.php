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

	public function __construct(Product $product, $specificationDataArray)
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
		  	echo '<hr><span color=red>deleting..</span><Br>';
		}  
		$this->removedAttributes = array();

		foreach ($this->attributes as $attribute)
		{
		  	echo '<hr>saving..<Br>';
		  	$attribute->save();
		}  
	}

	/**
	 * @todo implement
	 */
	public function toArray()
	{

	}

	private function loadSpecificationData($specificationDataArray)
	{
		foreach ($specificationDataArray as $specFieldID => $value)
		{
		  	$specField = SpecField::getInstanceByID($specFieldID);
		  	$specification = call_user_func(array($specField, 'getNewSpecificationInstance'), $this->product, $value);
		  	$this->attributes[$specField->getID()] = $specification;
		}		  
	}	
}

?>