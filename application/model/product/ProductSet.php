<?php


/**
 *
 * @package application/model/product
 * @author Integry Systems <http://integry.com>
 */
class ProductSet extends ARSet
{
	public function getChildProductIDs()
	{
		$f = new ARSelectFilter(new INCond(new ARFieldHandle('Product', 'parentID'), $this->getRecordIDs()));
		$q = new ARSelectQueryBuilder($f);
		$q->includeTable('Product');
		$q->setFilter($f);
		$q->removeFieldList();
		$q->addField('ID');
		$q->addField('parentID');

		$ids = array();
		foreach (ActiveRecordModel::getDataByQuery($q) as $row)
		{
			$ids[$row['parentID']][] = $row['ID'];
		}

		return $ids;
	}

	public function getVariationMatrix()
	{
		$ids = $prices = array();
		foreach ($this as $record)
		{
			$id = $record->getParent()->getID();
			$ids[] = $id;
			$prices[$id] = $record->getParent()->getPricingHandler()->toArray();
			$parents[$id] = $record->getParent()->toArray();
		}

		$f = new ARSelectFilter(new INCond(new ARFieldHandle('Product', 'parentID'), $ids));
		$children = ActiveRecordModel::getRecordSetArray('Product', $f, array('ProductImage'));
		if (!$children)
		{
			return array();
		}

		foreach ($children as &$child)
		{
			$child['Parent'] = $parents[$child['parentID']];
		}

		ProductPrice::loadPricesForRecordSetArray($children);

		$ids = array();
		foreach ($children as &$child)
		{
			$ids[] = $child['ID'];
			$products[$child['ID']] =& $child;
		}

		// calculate final children prices
		foreach ($children as &$child)
		{
			$setting = $child['childSettings']['price'];
			$child['finalPrice'] = $child['finalFormattedPrice'] = array();
			$parent = $prices[$child['parentID']];
			foreach ($parent['calculated'] as $id => $price)
			{
				$currency = Currency::getInstanceByID($id);
				$priceField = 'price_' . $id;

				if (!isset($child[$priceField]))
				{
					$child[$priceField] = 0;
				}

				$child['finalPrice'][$id] = $child[$priceField];
				$child['finalPrice'][$id] = $currency->roundPrice($child['finalPrice'][$id]);
				$child['finalFormattedPrice'][$id] = $currency->getFormattedPrice($child['finalPrice'][$id]);
			}
		}

		$f = new ARSelectFilter(new INCond(new ARFieldHandle('ProductVariationValue', 'productID'), $ids));
		$f->orderBy(new ARFieldHandle('ProductVariationType', 'position'));
		$f->orderBy(new ARFieldHandle('ProductVariation', 'position'));

		$productValues = array();
		$variations = array();
		$values = ActiveRecordModel::getRecordSetArray('ProductVariationValue', $f, array('ProductVariation', 'ProductVariationType'));
		foreach ($values as &$value)
		{
			$type = $value['ProductVariationType'];
			$parentID = $type['productID'];
			if (!isset($variations[$parentID][$type['ID']]))
			{
				$variations[$parentID][$type['ID']] = $type;
				$variations[$parentID][$type['ID']]['variations'] = array();
			}
			$variations[$parentID][$type['ID']]['variations'][] = $value;

			$productValues[$parentID][$value['productID']][$value['variationID']] =& $value;
		}

		$matrix = array();
		foreach ($productValues as $parentID => &$allValues)
		{
			foreach ($allValues as $product => &$values)
			{
				$matrix[$parentID][implode('-', array_keys($values))] = $products[$product];
			}
		}

		return array('products' => $matrix, 'variations' => $variations);
	}

	public function getVariationData(LiveCart $app)
	{
		$variations = $this->getVariationMatrix();

		if (!$variations)
		{
			return array();
		}

		$trackInventory = $app->getConfig()->get('INVENTORY_TRACKING') != 'DISABLE';

		foreach ($variations['products'] as $parentID => $products)
		{
			$variations['options'][$parentID] = array();

			// filter out unavailable products
			foreach ($variations['products'][$parentID] as $key => &$product)
			{
				if (!$product['isEnabled'] || ($trackInventory && ($product['stockCount'] <= 0)))
				{
					unset($variations['products'][$parentID][$key]);
				}
			}

			// get used variations
			$usedVariations = array();
			foreach ($variations['products'][$parentID] as $key => &$product)
			{
				$usedVariations = array_merge($usedVariations, explode('-', $key));
			}

			$usedVariations = array_flip($usedVariations);

			// prepare select options
			foreach ($variations['variations'][$parentID] as &$type)
			{
				$type['selectOptions'] = array();

				foreach ($type['variations'] as $variation)
				{
					$var = $variation['Variation'];

					if (isset($usedVariations[$var['ID']]))
					{
						$type['selectOptions'][$var['ID']] = $var['name_lang'];
					}
				}

				$variations['options'][$parentID] = $variations['options'][$parentID] + $type['selectOptions'];
			}

			// set used variation names
			foreach ($variations['products'][$parentID] as $key => &$product)
			{
				$product['variationNames'] = array_intersect_key($variations['options'][$parentID], array_flip(explode('-', $key)));
			}
		}

		return $variations;
	}

	public function loadVariations()
	{
		$f = new ARSelectFilter(new INCond(new ARFieldHandle('ProductVariationValue', 'productID'), $this->getRecordIDs()));
		$f->orderBy(new ARFieldHandle('ProductVariationType', 'position'));
		$f->orderBy(new ARFieldHandle('ProductVariation', 'position'));

		foreach (ActiveRecordModel::getRecordSet('ProductVariationValue', $f, array('ProductVariation', 'ProductVariationType')) as $value)
		{
			$value->product->registerVariation($value->variation);
		}
	}

	public static function loadVariationTypesForProductArray(&$array)
	{
		$index = self::getProductIndex($array);
		$f = new ARSelectFilter(new INCond(new ARFieldHandle('ProductVariationType', 'productID'), array_keys($index)));
		$f->orderBy(new ARFieldHandle('ProductVariationType', 'position'));

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
		$f->orderBy(new ARFieldHandle('ProductVariationType', 'position'));

		foreach (ActiveRecordModel::getRecordSetArray('ProductVariationValue', $f, array('ProductVariation', 'ProductVariationType')) as $value)
		{
			$product =& $index[$value['productID']];
			$product['variationTypes'][] = $value['ProductVariation'];
		}

		foreach ($array as &$product)
		{
			if (isset($product['variationTypes']))
			{
				$product['variationValues'] = array();
				foreach ($product['variationTypes'] as $type)
				{
					$product['variationValues'][] = $type['name_lang'];
				}
			}
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
			$product['Parent'] =& $parent;
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