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
	public function isApplicable($instance = null)
	{
		if (!$this->records)
		{
			return true;
		}

		$isApplicable = false;

		$instances = array();
		foreach ((array)$this->getOrders() as $order)
		{
			$instances = array_merge($instances, $order->getPurchasedItems());
		}
		$instances = array_merge($instances, $this->getContext()->getProducts());

		if ($instance)
		{
			$instances[] = $instance;
		}

		if ($instances)
		{
			$amount = null;

			$isAnyApplicable = false;

			foreach ($instances as $item)
			{
				$isApplicable = false;

				foreach ($this->records as $record)
				{
					if ($this->isInstanceApplicable($item, $record))
					{
						$isApplicable = $isAnyApplicable = true;
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

			if (!$isAnyApplicable)
			{
				return false;
			}
			else
			{
				$isApplicable = true;
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

	protected function getOrders()
	{
		$order = $this->getContext()->getOrder();
		return $order ? array($order) : array();
	}

	private function isInstanceApplicable($item, $record)
	{
		if ($item instanceof OrderedItem)
		{
			$product = $item->getProduct();
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

			$categoryIntervals = $product->getParent()->categoryIntervalCache->get();
		}
		else if (is_array($product))
		{
			$productID = $product['ID'];
			$parentID = isset($product['Parent']) ? $product['Parent']['ID'] : null;
			$manufacturerID = isset($product['Manufacturer']) ? $product['Manufacturer']['ID'] : null;

			$parent = isset($product['Parent']) ? $product['Parent'] : $product;
			$categoryIntervals = $parent['categoryIntervalCache'];
		}
		else
		{
			return false;
		}

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
				foreach (array_filter(explode(',', $categoryIntervals)) as $interval)
				{
					list($lft, $rgt) = explode('-', $interval);
					if (($lft >= $record['lft']) && ($rgt <= $record['rgt']))
					{
						$match = true;
						break;
					}
				}
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