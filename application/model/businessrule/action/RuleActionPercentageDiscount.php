<?php

ClassLoader::import('application.model.businessrule.RuleAction');

/**
 *
 * @author Integry Systems
 * @package application.model.businessrule.action
 */
class RuleActionPercentageDiscount extends RuleAction implements RuleItemAction
{
	public function applyToItem(OrderedItem $item)
	{
		$count = $item->count->get();
		$itemPrice = $item->getPriceWithoutTax();

		$discountPrice = $itemPrice - $this->getDiscountAmount($itemPrice);
		$discountStep = max($this->action->discountStep->get(), 1);
		$applicableCnt = floor($count / $discountStep);

		if ($this->action->discountLimit->get())
		{
			$applicableCnt = min($action->discountLimit->get(), $applicableCnt);
		}

		$subTotal = ($applicableCnt * $discountPrice) + (($count - $applicableCnt) * $itemPrice);
		$item->setItemPrice($subTotal / $count);
	}

	protected function getDiscountAmount($price)
	{
		return $price * ($this->action->amount->get() / 100);
	}
}

?>