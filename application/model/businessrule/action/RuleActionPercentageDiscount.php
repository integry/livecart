<?php

ClassLoader::import('application.model.businessrule.RuleAction');
ClassLoader::import('application.model.businessrule.interface.RuleItemAction');

/**
 *
 * @author Integry Systems
 * @package application.model.businessrule.action
 */
class RuleActionPercentageDiscount extends RuleAction implements RuleItemAction
{
	public function applyToItem(BusinessRuleProductInterface $item)
	{
		$count = $item->getCount();
		$itemPrice = $item->getPriceWithoutTax();
		$discountPrice = $itemPrice - $this->getDiscountAmount($itemPrice);
		$discountStep = max($this->getParam('discountStep'), 1);
		$applicableCnt = floor($count / $discountStep);

		if ($limit = $this->getParam('discountLimit'))
		{
			$applicableCnt = min($limit, $applicableCnt);
		}

		$subTotal = ($applicableCnt * $discountPrice) + (($count - $applicableCnt) * $itemPrice);
		$item->setItemPrice($subTotal / $count);
	}

	protected function getDiscountAmount($price)
	{
		return $price * ($this->getParam('amount', 100) / 100);
	}

	public static function getSortOrder()
	{
		return 1;
	}

	public function isItemAction()
	{
		return true;
	}

	public function isOrderAction()
	{
		return false;
	}
}

?>