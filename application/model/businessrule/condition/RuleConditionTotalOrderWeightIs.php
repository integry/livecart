<?php

/**
 *
 * @author Integry Systems
 * @package application/model/weight-pricing
 */
class RuleConditionTotalOrderWeightIs extends RuleCondition implements RuleOrderCondition
{
	public function isApplicable()
	{
		$order = $this->getContext()->getOrder();
		if(false == $order instanceof CustomerOrder)
		{
			return false;
		}
		$items = $order->getOrderedItems();
		$totalWeight = 0;
		foreach($items as $item)
		{
			$product = $item->productID;
			if($product)
			{
				$totalWeight += (float)$product->shippingWeight * $item->count;
			}
		}
		return $this->compareValues($this->toGrams($totalWeight), $this->params['subTotal']);
	}

	private function toGrams($weight)
	{
		return $weight * 1000;
	}
}

?>