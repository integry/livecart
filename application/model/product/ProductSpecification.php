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

	public function setProperty(SpecField $field, $value)
	{
		$specItem = SpecificationItem::getNewInstance($this->product, $field, $value);
		$specItem->save();
	}

	private function loadSpecificationItems()
	{
		$specItemSet = ActiveRecordModel::getRecordSet("SpecificationItem");
	}

	public function getRelatedProductArray()
	{
	}

	public function getRelatedProductSet()
	{
	}

	/**
	 * Gets an array of properties assigned to a product (specification array)
	 *
	 * @return array
	 */
	public function getSpecificationDataArray()
	{
		$filter = new ARSelectFilter();
		$cond = new OperatorCond(new ARFieldHandle("Product", "ID"), $this->product->getID(), "=");
		$filter->setCondition($cond);
		$filter->setOrder(new ARFieldHandle("SpecField", "position"));

		$specDataArray = ActiveRecordModel::getRecordSetArray("SpecificationItem", $filter, SpecificationItem::LOAD_REFERENCES);
		return $specDataArray;
	}

	/**
	 * Gets a record set of properties assigned to a product (specification set)
	 *
	 */
	public function getSpecificationDataSet()
	{
		$itemSet = ActiveRecordModel::getRecordSet("SpecificationItem", $this->getSpecificationFilter(), SpecificationItem::LOAD_REFERENCES);
		return $itemSet;
	}

	private function getSpecificationFilter()
	{
		$filter = new ARSelectFilter();
		$filter->setOrder(new ARFieldHandle("SpecField", "position"));
		return $filter;
	}

	public function load()
	{
	}

	public function toArray()
	{
	}
}

?>