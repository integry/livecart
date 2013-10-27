<?php

ClassLoader::importNow("application/model/product/ProductVariationTypeSet");

/**
 * Product variations
 *
 * @package application/controller/backend
 * @author Integry Systems
 * @role option
 */
class ProductVariationController extends StoreManagementController
{
	/**
	 */
	public function indexAction()
	{


		$parent = Product::getInstanceByID($this->request->get('id'), true);

		$variationTypes = $parent->getRelatedRecordSet('ProductVariationType');
		$variations = $variationTypes->getVariations();
		$parentArray = $parent->toArray();

		$this->set('parent', $parentArray);
		$this->set('params', array(
									'parent' => $parentArray,
									'variationTypes' => $variationTypes->toArray(),
									'variations' => $variations->toArray(),
									'matrix' => $parent->getVariationMatrix(),
									'currency' => $this->application->getDefaultCurrencyCode()
									));

	}

	public function saveAction()
	{
		ActiveRecordModel::beginTransaction();

		$parent = Product::getInstanceByID($this->request->get('id'), true);

		$items = json_decode($this->request->get('items'), true);
		$types = json_decode($this->request->get('types'), true);
		$variations = json_decode($this->request->get('variations'), true);

		$existingTypes = $existingVariations = $existingItems = array();
		$currency = $this->application->getDefaultCurrencyCode();

		// deleted types
		foreach ($types as $id)
		{
			if (is_numeric($id))
			{
				$existingTypes[] = $id;
			}
		}

		$parent->deleteRelatedRecordSet('ProductVariationType', new ARDeleteFilter(new NotINCond('ProductVariationType.ID', $existingTypes)));

		// deleted variations
		foreach ($variations as $type => $typeVars)
		{
			foreach ($typeVars as $id)
			{
				if (is_numeric($id))
				{
					$existingVariations[] = $id;
				}
			}
		}

		$f = new ARDeleteFilter(new INCond('ProductVariation.typeID', $existingTypes));
		$f->andWhere(new NotINCond('ProductVariation.ID', $existingVariations));
		ActiveRecordModel::deleteRecordSet('ProductVariation', $f);

		// deleted items
		foreach ($items as $id)
		{
			if (is_numeric($id))
			{
				$existingItems[] = $id;
			}
		}

		$parent->deleteRelatedRecordSet('Product', new ARDeleteFilter(new NotINCond('Product.ID', $existingItems)));

		// load existing records
		foreach (array('Types' => 'ProductVariationType', 'Variations' => 'ProductVariation', 'Items'  => 'Product') as $arr => $class)
		{
			$var = 'existing' . $arr;
			$array = $$var;
			if ($array)
			{
				ActiveRecordModel::getRecordSet($class, new ARSelectFilter(new INCond(new ARFieldHandle($class, 'ID'), $array)));
			}
		}

		$idMap = array();

		// save types
		$request = $this->request->toArray();
		foreach ($types as $index => $id)
		{
			if (!is_numeric($id))
			{
				$type = ProductVariationType::getNewInstance($parent);
				$idMap[$id] = $type;
			}
			else
			{
				$type = ProductVariationType::getInstanceByID($id);
			}

			$type->setValueByLang('name', null, $request['variationType'][$index]);
			$type->position->set($index);

			if (!empty($request['typeLang_' . $id]))
			{
				foreach ($request['typeLang_' . $id] as $field => $value)
				{
					list($field, $lang) = explode('_', $field, 2);
					$type->setValueByLang($field, $lang, $value);
				}
			}

			$type->save();
		}

		// save variations
		$tree = array();
		$typeIndex = -1;
		foreach ($variations as $typeID => $typeVars)
		{
			$type = is_numeric($typeID) ? ProductVariationType::getInstanceByID($typeID) : $idMap[$typeID];
			$typeIndex++;

			foreach ($typeVars as $index => $id)
			{
				if (!is_numeric($id))
				{
					$variation = ProductVariation::getNewInstance($type);
					$idMap[$id] = $variation;
				}
				else
				{
					$variation = ProductVariation::getInstanceByID($id);
				}

				$variation->position->set($index);
				$variation->setValueByLang('name', null, $request['variation'][$id]);

				if (!empty($request['variationLang_' . $id]))
				{
					foreach ($request['variationLang_' . $id] as $field => $value)
					{
						list($field, $lang) = explode('_', $field, 2);
						$variation->setValueByLang($field, $lang, $value);
					}
				}

				$variation->save();

				$tree[$typeIndex][] = $variation;
			}
		}

		$images = array();

		// save items
		foreach ($items as $index => $id)
		{
			if (!is_numeric($id))
			{
				$item = $parent->createChildProduct();
				$idMap[$id] = $item;
			}
			else
			{
				$item = Product::getInstanceByID($id);
			}

			$item->isEnabled->set(!empty($request['isEnabled'][$id]));

			if (!$request['sku'][$index])
			{
				$request['sku'][$index] = $item->sku;
			}

			foreach (array('sku', 'stockCount', 'shippingWeight') as $field)
			{
				if ($item->$field || $request[$field][$index])
				{
					$item->$field->set($request[$field][$index]);
				}
			}

			$item->setChildSetting('weight', $request['shippingWeightType'][$index]);
			$item->setChildSetting('price', $request['priceType'][$index]);

			if (!strlen($request['priceType'][$index]))
			{
				$request['price'][$index] = '';
			}

			$item->setPrice($currency, $request['price'][$index]);

			$item->save();

			// assign variations
			$currentVariationValues = $currentVariations = array();
			foreach ($item->getRelatedRecordSet('ProductVariationValue') as $variationValue)
			{
				$currentVariations[$variationValue->variation->getID()] = $variationValue->variation;
				$currentVariationValues[$variationValue->variation->getID()] = $variationValue;
			}

			foreach ($this->getItemVariations($tree, $index) as $variation)
			{
				if (!isset($currentVariations[$variation->getID()]))
				{
					ProductVariationValue::getNewInstance($item, $variation)->save();
				}

				unset($currentVariations[$variation->getID()]);
			}

			foreach ($currentVariations as $deletedVariation)
			{
				$currentVariationValues[$deletedVariation->getID()]->delete();
			}

			// set image
			if ($_FILES['image']['tmp_name'][$index])
			{
				if ($item->defaultImage)
				{
					$item->defaultImage->load();
					$image = $item->defaultImage;
				}
				else
				{
					$image = ProductImage::getNewInstance($item);
				}

				$image->save();
				$image->setFile($_FILES['image']['tmp_name'][$index]);
				$image->save();

				$images[$item->getID()] = $image->toArray();
				unset($images[$item->getID()]['Product']);
			}
		}

		ActiveRecordModel::commit();

		// pass ID's for newly created records
		$ids = array();
		foreach ($idMap as $id => $instance)
		{
			$ids[$id] = $instance->getID();
		}

		$this->set('ids', $ids);
		$this->set('parent', $parent->getID());
		$this->set('images', $images);
		$this->set('variationCount', $parent->getRelatedRecordCount('Product', query::query()->where('Product.isEnabled = :Product.isEnabled:', array('Product.isEnabled' => true))));
	}

	/**
	 *	Given the item index, find the applicable variations
	 */
	private function getItemVariations($tree, $index)
	{
		$variations = array();
		for ($k = count($tree) - 1; $k >= 0; $k--)
		{
			$count = count($tree[$k]);
			$varIndex = $index % $count;
			$variation = $tree[$k][$varIndex];
			$variations[$variation->getID()] = $variation;

			$index = ceil(($index + 1) / $count) - 1;
		}

		return $variations;
	}
}

?>