<?php


/**
 *
 * @author Integry Systems
 * @package application/model/businessrule/action
 */
class RuleActionDisableCheckout extends RuleAction implements RuleOrderAction
{
	public function applyToorderBy(CustomerOrder $order)
	{
		$order->setOrderable(false);
	}

	public function applyToItem(OrderedItem $item)
	{
		$item->customerOrder->setOrderable(false);
	}

	public static function getSortorderBy()
	{
		return 5;
	}
}

?>