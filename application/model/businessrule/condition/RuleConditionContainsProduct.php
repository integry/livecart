<?php

ClassLoader::import('application.model.businessrule.RuleCondition');
ClassLoader::import('application.model.businessrule.interface.RuleOrderCondition');

/**
 *
 * @author Integry Systems
 * @package application.model.businessrule.condition
 */
class RuleConditionContainsProduct extends RuleCondition implements RuleOrderCondition
{
	private $containedProducts;

	public function isApplicable($instance = null)
	{
		if (!$this->records)
		{
			return true;
		}

		$isApplicable = false;

		$order = $this->getContext()->getOrder();

		$instances = $order ? $order->getShoppingCartItems() : array();
		$instances = array_merge($instances, $this->getContext()->getProducts());
		if ($instance)
		{
			$instances[] = $instance;
		}

		if ($instances)
		{
			$amount = null;

			foreach ($this->records as $record)
			{
				$isApplicable = false;

				foreach ($instances as $item)
				{
					if ($this->isInstanceApplicable($item, $record))
					{
						$isApplicable = true;

						if (!is_null($this->params['subTotal']))
						{
							$amount += $item->getSubTotal();
						}
						else if (!is_null($this->params['count']))
						{
							$amount += $item->getCount();
						}
					}
				}

				if (!$isApplicable && !$this->params['isAnyRecord'])
				{
					return false;
				}
			}

			if (!is_null($amount))
			{
				$compare = !is_null($this->params['count']) ? $this->params['count'] : $this->params['subTotal'];
				$isApplicable = $this->compareValues($amount, $compare, $this->params['comparisonType']);
			}

			return $isApplicable;
		}
	}

	public function isProductApplicable($product)
	{
		foreach ($this->records as $record)
		{
			if ($this->isInstanceApplicable($product, $record))
			{
				return true;
			}
		}

		return false;
	}

	private function isInstanceApplicable($item, $record)
	{
		if ($item instanceof OrderedItem)
		{
			$product = $item->product->get();
		}
		else if ($item instanceof Product)
		{
			$product = $item;
		}
		else if ($item instanceof RuleProductContainer)
		{
			$product = $item->getProduct();
		}
		else
		{
			$product = $item;
		}

		if (is_object($product))
		{
			$productID = $product->getID();
			$parentID = $product->getParent()->getID();
			$manufacturerID = $product->manufacturer->get() ? $product->manufacturer->get()->getID() : null;

			$category = $product->getCategory();
			$lft = $category->lft->get();
			$rgt = $category->rgt->get();
		}
		else if (is_array($product))
		{
			$productID = $product['ID'];
			$parentID = isset($product['Parent']) ? $product['Parent']['ID'] : null;
			$manufacturerID = isset($product['Manufacturer']) ? $product['Manufacturer']['ID'] : null;

			$parent = isset($product['Parent']) ? $product['Parent'] : $product;
			if (isset($parent['Category']))
			{
				$lft = $parent['Category']['lft'];
				$rgt = $parent['Category']['rgt'];
			}
		} else { var_dump($product); exit; }

		$match = false;

		switch ($record['class'])
		{
			case 'Product':
				$match = in_array($record['ID'], array($productID, $parentID));
				break;

			case 'Manufacturer':
				$match = ($manufacturerID == $record['ID']);
				break;

			case 'Category':
				$match = ($lft >= $record['lft']) && ($rgt <= $record['rgt']);
				break;
		}

		return $match;
	}

	public static function getSortOrder()
	{
		return 1;
	}
}

?>