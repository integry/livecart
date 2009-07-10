<?php

ClassLoader::import('application.model.businessrule.action.RuleActionPercentageDiscount');
ClassLoader::import('application.model.businessrule.interface.RuleItemAction');
ClassLoader::import('application.model.businessrule.interface.RuleOrderAction');

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
		$orderDiscount->description->set($this->parentCondition->getParam('name_lang'));
		$order->registerOrderDiscount($orderDiscount);
	}

	protected function getDiscountAmount($price)
	{
		return $this->getParam('amount');
	}

	public static function getSortOrder()
	{
		return 2;
	}
}

?>