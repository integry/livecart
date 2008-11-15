<?php

ClassLoader::import('library.activerecord.ARSet');

/**
 *
 * @package application.model.product
 * @author Integry Systems <http://integry.com>
 */
class ProductSet extends ARSet
{
	public function loadVariations()
	{
		$f = new ARSelectFilter(new INCond(new ARFieldHandle('ProductVariationValue', 'productID'), $this->getRecordIDs()));
		$f->setOrder(new ARFieldHandle('ProductVariationType', 'position'));
		$f->setOrder(new ARFieldHandle('ProductVariation', 'position'));

		foreach (ActiveRecordModel::getRecordSet('ProductVariationValue', $f, array('ProductVariation', 'ProductVariationType')) as $value)
		{
			$value->product->get()->registerVariation($value->variation->get());
		}
	}

	public static function loadVariationTypesForProductArray(&$array)
	{
		$index = self::getProductIndex($array);
		$f = new ARSelectFilter(new INCond(new ARFieldHandle('ProductVariationType', 'productID'), array_keys($index)));
		$f->setOrder(new ARFieldHandle('ProductVariationType', 'position'));

		foreach (ActiveRecordModel::getRecordSetArray('ProductVariationType', $f) as $value)
		{
			$product =& $index[$value['productID']];
			$product['variationTypes'][] = $value;
		}
	}

	public static function loadVariationsForProductArray(&$array)
	{
		$index = self::getProductIndex($array);
		$f = new ARSelectFilter(new INCond(new ARFieldHandle('ProductVariationValue', 'productID'), array_keys($index)));
		$f->setOrder(new ARFieldHandle('ProductVariationType', 'position'));

		foreach (ActiveRecordModel::getRecordSetArray('ProductVariationValue', $f, array('ProductVariation', 'ProductVariationType')) as $value)
		{
			$product =& $index[$value['productID']];
			$product['variationTypes'][] = $value['ProductVariation'];
		}
	}

	public static function loadChildrenForProductArray(&$array)
	{
		$index = self::getProductIndex($array);

		$f = new ARSelectFilter(new INCond(new ARFieldHandle('Product', 'parentID'), array_keys($index)));
		$products = ActiveRecordModel::getRecordSetArray('Product', $f, array('ProductImage'));

		self::loadVariationsForProductArray($products);
		ProductPrice::loadPricesForRecordSetArray($products);

		foreach ($products as $product)
		{
			$parent =& $index[$product['parentID']];
			$parent['children'][] = $product;
		}
	}

	private function getProductIndex(&$array)
	{
		$index = array();
		foreach ($array as &$product)
		{
			$index[$product['ID']] =& $product;
		}

		return $index;
	}
}

?>