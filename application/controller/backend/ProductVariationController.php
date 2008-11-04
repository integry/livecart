<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.product.Product");
ClassLoader::import("application.model.product.ProductVariationType");

/**
 * Product variations
 *
 * @package application.controller.backend
 * @author Integry Systems
 * @role option
 */
class ProductVariationController extends StoreManagementController
{
	/**
	 * @return ActionResponse
	 */
	public function index()
	{
		$response = new ActionResponse();

		$parent = Product::getInstanceByID($this->request->get('id'), true);

		$variationTypes = $parent->getRelatedRecordSet('ProductVariationType');
		$variations = $variationTypes->getVariations();
		$parentArray = $parent->toArray();

		$response->set('parent', $parentArray);
		$response->set('params', array(
									'parent' => $parentArray,
									'variationTypes' => $variationTypes->toArray(),
									'variations' => $variations->toArray(),
									'matrix' => $parent->getVariationMatrix(),
									'currency' => $this->application->getDefaultCurrencyCode()
									));

		return $response;
	}

	public function save()
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

		$parent->deleteRelatedRecordSet('ProductVariationType', new ARDeleteFilter(new NotINCond(new ARFieldHandle('ProductVariationType', 'ID'), $existingTypes)));

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

		$f = new ARDeleteFilter(new INCond(new ARFieldHandle('ProductVariation', 'typeID'), $existingTypes));
		$f->mergeCondition(new NotINCond(new ARFieldHandle('ProductVariation', 'ID'), $existingVariations));
		ActiveRecordModel::deleteRecordSet('ProductVariation', $f);

		// deleted items
		foreach ($items as $id)
		{
			if (is_numeric($id))
			{
				$existingItems[] = $id;
			}
		}

		$parent->deleteRelatedRecordSet('Product', new ARDeleteFilter(new NotINCond(new ARFieldHandle('Product', 'ID'), $existingItems)));

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
				$type = ActiveRecordModel::getInstanceByID('ProductVariationType', $id);
			}

			$type->setValueByLang('name', null, $request['variationType'][$index]);
			$type->position->set($index);

			$type->save();
		}

		// save variations
		$tree = array();
		$typeIndex = -1;
		foreach ($variations as $typeID => $typeVars)
		{
			$type = is_numeric($typeID) ? ActiveRecordModel::getInstanceByID('ProductVariationType', $typeID) : $idMap[$typeID];
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
					$variation = ActiveRecordModel::getInstanceByID('ProductVariation', $id);
				}

				$variation->position->set($index);
				$variation->setValueByLang('name', null, $request['variation'][$id]);
				$variation->save();

				$tree[$typeIndex][] = $variation;
			}
		}

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
				$item = ActiveRecordModel::getInstanceByID('Product', $id);
			}

			foreach (array('sku', 'stockCount', 'shippingWeight') as $field)
			{
				if ($item->$field->get() || $request[$field][$index])
				{
					$item->$field->set($request[$field][$index]);
				}
			}

			$item->setChildSetting('weight', $request['shippingWeightType'][$index]);
			$item->setChildSetting('price', $request['priceType'][$index]);
			$item->setPrice($currency, $request['price'][$index]);

			$item->isEnabled->set(true);

			$item->save();

			// assign variations
			$currentVariationValues = $currentVariations = array();
			foreach ($item->getRelatedRecordSet('ProductVariationValue') as $variationValue)
			{
				$currentVariations[$variationValue->variation->get()->getID()] = $variationValue->variation->get();
				$currentVariationValues[$variationValue->variation->get()->getID()] = $variationValue;
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

		}

		ActiveRecordModel::commit();

		// pass ID's for newly created records
		$ids = array();
		foreach ($idMap as $id => $instance)
		{
			$ids[$id] = $instance->getID();
		}

		$response = new ActionResponse('ids', $ids);
		$response->set('parent', $parent->getID());
		return $response;
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