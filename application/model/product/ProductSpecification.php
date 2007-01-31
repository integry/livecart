<?php

ClassLoader::import("application.model.product.SpecificationItem");

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
	public function setAttribute(Specification $specification)
	{
		$this->attributes[$specification->getSpecField()->getID()] = $specification;
	}

	/**
	 * Removes persisted product specification property
	 *
	 */
	public function removeAttribute(SpecField $field)
	{
		$this->removedAttributes[$field->getID()] = $this->attributes[$field->getID()];
		unset($this->attributes[$field->getID()]);
	}

	public function isAttributeSet(SpecField $field)
	{
		return isset($this->attributes[$field->getID()]);  
	}
	
	public function getAttribute(SpecField $field)
	{
		if (!$this->isAttributeSet($field))
		{
		  	$this->attributes[$field->getID()] = $field->getNewSpecificationInstance($this->product, null);
		  	echo 'creating new <Br>';
		}
		else
		{
		  	echo 'restoring <Br>';				  
		}

		return $this->attributes[$field->getID()];  	
	}

	public function save()
	{
		foreach ($this->removedAttributes as $attribute)
		{
		  	$attribute->delete();
		}  

		foreach ($this->attributes as $attribute)
		{
		  	echo '<hr>saving..<Br>';
		  	$attribute->save();
		  	echo (int)$attribute->value->isModified() . '<Br>';
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