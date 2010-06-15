<?php

ClassLoader::import('application.model.businessrule.RuleAction');

/**
 *
 * @author Integry Systems
 * @package application.model.businessrule.action
 */
class RuleActionDisableCheckout extends RuleAction implements RuleOrderAction
{
	public function applyToOrder(CustomerOrder $order)
	{
		$order->setOrderable(false);
	}

	public function applyToItem(OrderedItem $item)
	{
		$item->customerOrder->get()->setOrderable(false);
	}

	public static function getSortOrder()
	{
		return 5;
	}
}

?>