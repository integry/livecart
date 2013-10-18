<?php


/**
 *
 * @author Integry Systems
 * @package application/model/businessrule/action
 */
class RuleActionFixedDiscount extends RuleActionPercentageDiscount implements RuleOrderAction, RuleItemAction
{
	public function applyToOrder(CustomerOrder $order)
	{
		$amount = $order->getCurrency()->convertAmount(CustomerOrder::getApplication()->getDefaultCurrency(), $this->getDiscountAmount($order->totalAmount));
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

	public function isItemAction()
	{
		return DiscountAction::TYPE_ORDER_DISCOUNT != $this->getParam('type');
	}

	public function isOrderAction()
	{
		return DiscountAction::TYPE_ORDER_DISCOUNT == $this->getParam('type');
	}
}

?>