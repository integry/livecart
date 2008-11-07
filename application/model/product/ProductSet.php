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
}

?>