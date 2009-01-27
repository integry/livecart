<?php

ClassLoader::import('application.model.product.Product');

/**
 * Logic for product comparison feature
 *
 * @package application.model.product
 * @author Integry Systems <http://integry.com>
 */
class ProductCompare
{
	private $application;

	public function __construct(LiveCart $application)
	{
		$this->application = $application;
	}

	public function hasProducts()
	{
		return count($this->getComparedProductIDs()) > 0;
	}

	public function addToCompare($ids)
	{
		$current = $this->getComparedProductInfo();

		$filter = Category::getRootNode()->getProductFilter(select(IN('Product.ID', $ids)));
		$products = ActiveRecord::getRecordSetArray('Product', $filter);

		foreach ($products as $product)
		{
			$current[$product['ID']] = array_intersect_key($product, array_flip(array('ID', 'name_lang')));
		}

		$this->application->getSession()->set('compare', $current);
	}

	public function addProductByID($id)
	{
		$current = $this->getComparedProductIDs();
		$this->addToCompare(array($id));

		// return ID of the added product
		return array_pop(array_diff($this->getComparedProductIDs(), $current));
	}

	public function removeProductById($id)
	{
		$current = $this->getComparedProductInfo();
		unset($current[$id]);
		$this->application->getSession()->set('compare', $current);
	}

	public function &getCompareData()
	{
		return $this->sortByCategory($this->getComparedProducts());
	}

	private function &sortByCategory(&$products)
	{
		// get used category IDs
		$attrCats = array();
		foreach ($products as &$product)
		{
			if (!empty($product['attributes']))
			{
				foreach ($product['attributes'] as $attr)
				{
					$attrCats[$attr['SpecField']['categoryID']] = true;
				}
			}
		}

		$attrCats[Category::ROOT_ID] = true;

		$categories = array();
		foreach (ActiveRecord::getRecordSetArray('Category', select(IN('Category.ID', array_keys($attrCats)))) as $cat)
		{
			$categories[$cat['ID']] = $cat;
		}

		// determine the lowest level category for each product and group products
		$sorted = array();
		foreach ($products as &$product)
		{
			if (!empty($product['attributes']))
			{
				$maxLft = 0;
				foreach ($product['attributes'] as $attr)
				{
					$cat = $categories[$attr['SpecField']['categoryID']];
					if ($cat['lft'] > $maxLft)
					{
						$maxLft = $cat['lft'];
						$catID = $cat['ID'];
					}
				}
			}
			else
			{
				$catID = Category::ROOT_ID;
			}

			$sorted[$catID]['products'][] =& $product;
			if (!isset($sorted[$catID]['category']))
			{
				$sorted[$catID]['category'] =& $categories[$catID];
			}
		}

		// get used attributes and sort them
		foreach ($sorted as &$category)
		{
			foreach ($category['products'] as &$product)
			{
				if (!empty($product['attributes']))
				{
					foreach ($product['attributes'] as &$attr)
					{
						$field = $attr['SpecField'];
						$groupPosition = -1;
						if (!empty($field['specFieldGroupID']))
						{
							$groupPosition = $field['SpecFieldGroup']['position'];
							$category['groups'][$groupPosition]['group'] =& $field['SpecFieldGroup'];
						}

						$category['groups'][$groupPosition]['attributes'][$field['position']] = $field;
					}
				}
			}

			if (!empty($category['groups']))
			{
				ksort($category['groups']);
				foreach ($category['groups'] as &$group)
				{
					ksort($group['attributes']);
				}
			}
		}

		// sort by category order
		usort($sorted, array($this, 'sortCategories'));

		return $sorted;
	}

	private function sortCategories($a, $b)
	{
		if ($a['category']['lft'] == $b['category']['lft'])
		{
			return 0;
		}

		return $a['category']['lft'] > $b['category']['lft'] ? 1 : -1;
	}

	private function getComparedProducts()
	{
		$filter = Category::getRootNode()->getProductFilter(select(IN('Product.ID', $this->getComparedProductIDs())));
		$products = ActiveRecord::getRecordSetArray('Product', $filter, array('Category', 'ProductImage'));
		ProductSpecification::loadSpecificationForRecordSetArray($products, true);
		ProductPrice::loadPricesForRecordSetArray($products);
		return $products;
	}

	public function getComparedProductIDs()
	{
		return array_keys($this->application->getSession()->get('compare', array()));
	}

	public function getComparedProductInfo()
	{
		return $this->application->getSession()->get('compare', array());
	}

}

?>