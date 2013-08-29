<?php


/**
 * Links multiple pre-defined attribute values (of the same attribute) to a product
 *
 * @package application/model/specification
 * @author Integry Systems <http://integry.com>
 */
class EavMultiValueItem extends EavMultiValueItemCommon
{
	public function getItemClassName()
	{
		return 'EavItem';
	}

	public static function getNewInstance(EavObject $product, EavField $field, $value = false)
	{
		return parent::getNewInstance(__CLASS__, $product, $field, $value);
	}

	public static function restoreInstance(EavObject $product, EavField $field, $specValues)
	{
		return parent::restoreInstance(__CLASS__, $product, $field, $specValues);
	}

	protected function __construct(EavObject $product, EavField $field)
	{
		parent::__construct($product, $field);
	}

	public function set(EavValue $value)
	{
	  	return parent::set($value);
	}

	public function removeValue(EavValue $value)
	{
	  	return parent::removeValue($value);
	}

	protected function setItem(EavItem $item)
	{
		return parent::setItem($item);
	}
}

?>