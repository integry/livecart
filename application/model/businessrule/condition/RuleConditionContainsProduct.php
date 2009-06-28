<?php

ClassLoader::import('application.model.businessrule.RuleCondition');

/**
 *
 * @author Integry Systems
 * @package application.model.businessrule.condition
 */
class RuleConditionContainsProduct extends RuleCondition
{
	private $containedProducts

	public function isApplicable($instance = null)
	{
		$isApplicable = false;

		$order = $this->getContext()->getOrder();

		if ($order)
		{
			$amount = null;

			foreach ($this->records as $record)
			{
				$instances = $order->getShoppingCartItems();
				if ($instance)
				{
					$instances[] = $instance;
				}

				foreach ($instances as $item)
				{
					if ($item instanceof OrderedItem)
					{
						$product = $item->product->get();
					}
					else if ($item instanceof Product)
					{
						$product = $item;
					}

					if (is_object($product))
					{
						$productID = $product->getID();
						$parentID = $product->getParent()->getID();
						$manufacturerID = $product->manufacturer->get()->getID();
					}
					else if (is_array($product))
					{
						$productID = $product['ID'];
						$parentID = isset($product['Parent']) ? $product['Parent']['ID'] : null;
						$manufacturerID = isset($product['Manufacturer']) ? $product['Manufacturer']['ID'] : null;
					}

					if (!empty($record['productID']))
					{
						$match = in_array($record['productID'], array($productID, $parentID));
					}
					else if (!empty($record['manufacturerID']))
					{
						$match = ($manufacturerID == $record['manufacturerID']);
					}
					else if (!empty($record['categoryID']))
					{
						// @todo ...
					}

					if ($match)
					{
						$isApplicable = true;

						if (!is_null($this->params['subTotal']))
						{
							$amount += $item->getSubTotal();
						}
						else if (!is_null($this->params['count']))
						{
							$amount += $item->count->get();
						}
					}
					else
					{
						if (!$this->params['isAnyRecord'])
						{
							return false;
						}
					}
				}
			}

			if (!is_null($amount))
			{
				$compare = is_null($this->params['count']) ? $this->params['count'] : $this->params['subTotal'];
				$isMatching = $this->compareValues($amount, $compare, $this->params['comparisonType']);
			}

			return $isMatching;
		}
	}
}

?>