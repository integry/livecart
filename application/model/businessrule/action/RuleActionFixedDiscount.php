<?php

ClassLoader::import('application.model.businessrule.action.RuleActionPercentageDiscount');

/**
 *
 * @author Integry Systems
 * @package application.model.businessrule.action
 */
class RuleActionFixedDiscount extends RuleActionPercentageDiscount implements RuleOrderAction, RuleItemAction
{
	public function applyToOrder(CustomerOrder $order)
	{
		$amount = $order->getCurrency()->convertAmount(CustomerOrder::getApplication()->getDefaultCurrency(), $this->getDiscountAmount(0));
		$orderDiscount = OrderDiscount::getNewInstance($order);
		$orderDiscount->amount->set($amount);
		$orderDiscount->description->set($this->action->condition->get()->getValueByLang('name'));
		$order->registerOrderDiscount($orderDiscount);
	}

	protected function getDiscountAmount($price)
	{
		return $this->action->amount->get();
	}
}

?>