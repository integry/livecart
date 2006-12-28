<?php

ClassLoader::import("application.model.product.SpecificationItem");

/**
 * Product specification wrapper class
 * Loads/modifies product specification data
 *
 * @author Saulius Rupainis <saulius@integry.net>
 * @package application.model.product
 */
class ProductSpecification //implements IteratorAggregate
{
	private $product = null;

	public function __construct(Product $product)
	{
		$this->product = $product;
		$this->product->load();
	}

	/**
	 * Sets specification property by mapping product, specification field, and
	 * assigned value to one record (atomic item)
	 *
	 * @param SpecField $field
	 * @param SpecFieldValue $value
	 */
	public function setProperty(SpecField $field, SpecFieldValue $value)
	{
		$specItem = ActiveRecordModel::getNewInstance("SpecificationItem");

		$specItem->product = $this->product;
		$specItem->specFieldValue = $value;
		$specItem->specField = $field;

		$specItem->save();
	}

	/**
	 * Removes persisted product specification property
	 *
	 */
	public function removeProperty(SpecField $field)
	{
	}

	public function removePropertyValue(SpecFieldValue $value)
	{
	}

	private function loadSpecificationItems()
	{
		$specItemSet = ActiveRecordModel::getRecordSet("SpecificationItem");
	}
}

?>