<?php


/**
 * Product specification wrapper class. Loads/modifies product specification data.
 *
 * This class usually should not be used directly as most of the attribute manipulations
 * can be done with Product class itself.
 *
 * @package application/model/product
 * @author Integry Systems <http://integry.com>
 */
class ProductSpecification extends EavSpecificationManagerCommon
{
	public function __construct(Product $product, $specificationDataArray = array())
	{
		parent::__construct($product, $specificationDataArray);
	}

	public function getFieldClass()
	{
		return 'SpecField';
	}

	public function getSpecificationFieldSet($loadReferencedRecords = false)
	{
		$category = $this->owner->getCategory();

		if ($category)
		{
			return $category->getSpecificationFieldSet(Category::INCLUDE_PARENT, $loadReferencedRecords);
		}
		else
		{
			return array();
		}
	}

	/**
	 * Removes persisted product specification property
	 *
	 *	@param SpecField $field SpecField instance
	 */
	public function removeAttribute(SpecField $field)
	{
		return parent::removeAttribute($field);
	}

	public function removeAttributeValue(SpecField $field, SpecFieldValue $value)
	{
		return parent::removeAttributeValue($field, $value);
	}

	public function isAttributeSet(SpecField $field)
	{
		return parent::isAttributeSet($field);
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
		return parent::getAttribute($field, $defaultValue);
	}

	public static function loadSpecificationForProductArray(&$productArray)
	{
		return parent::loadSpecificationForRecordArray($productArray);
	}

	/**
	 * Load product specification data for a whole array of products at once
	 */
	public static function loadSpecificationForRecordSetArray(&$productArray, $fullSpecification = false)
	{
		return parent::loadSpecificationForRecordSetArray(__CLASS__, $productArray, $fullSpecification);
	}

	protected static function fetchSpecificationData($productIDs, $fullSpecification = false)
	{
		return parent::fetchSpecificationData(__CLASS__, $productIDs, $fullSpecification);
	}
}

?>