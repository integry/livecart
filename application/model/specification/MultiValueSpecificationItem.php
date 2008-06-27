<?php

ClassLoader::import('application.model.eavcommon.EavMultiValueItemCommon');

/**
 * Links multiple pre-defined attribute values (of the same attribute) to a product
 *
 * @package application.model.specification
 * @author Integry Systems <http://integry.com>
 */
class MultiValueSpecificationItem extends EavMultiValueItemCommon
{
	public function getItemClassName()
	{
		return 'SpecificationItem';
	}

	public static function getNewInstance(Product $product, SpecField $field, $value = false)
	{
		return parent::getNewInstance(__CLASS__, $product, $field, $value);
	}

	public static function restoreInstance(Product $product, SpecField $field, $specValues)
	{
		return parent::restoreInstance(__CLASS__, $product, $field, $specValues);
	}

	protected function __construct(Product $product, SpecField $field)
	{
		parent::__construct($product, $field);
	}

	public function set(SpecFieldValue $value)
	{
	  	return parent::set($value);
	}

	public function removeValue(SpecFieldValue $value)
	{
	  	return parent::removeValue($value);
	}

	protected function setItem(SpecificationItem $item)
	{
		return parent::setItem($item);
	}
}

?>