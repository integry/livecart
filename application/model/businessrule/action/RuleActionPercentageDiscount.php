<?php


/**
 *
 * @author Integry Systems
 * @package application/model/businessrule/action
 */
class RuleActionPercentageDiscount extends RuleAction implements RuleItemAction
{
	private $calls = 0;

	public function applyToItem(BusinessRuleProductInterface $item)
	{
		$count = $item->getCount();
		$itemPrice = $item->getPriceWithoutTax();
		$discountPrice = $itemPrice - $this->getDiscountAmount($itemPrice);
		$discountStep = max($this->getParam('discountStep'), 1);

		$applicableCnt = floor($count / $discountStep);
		$limit = $this->getParam('discountLimit');

		if ($this->getParam('isOrderLevel'))
		{
			if ($this->getContext()->getOrder())
			{
				$step = 0;
				$previousItems = 0;
				$totalCount = 0;
				$stop = false;

				$items = $this->getContext()->getOrder()->getShoppingCartItems();
				usort($items, array($this, 'sortByPrice'));

				// calculate total number of applicable item products and how many have higher priority (have already been processed)
				foreach ($items as $orderedItem)
				{
					if ($this->isItemApplicable($orderedItem))
					{
						$totalCount += $orderedItem->getCount();

						if ($orderedItem === $item)
						{
							$stop = true;
						}

						if ($stop)
						{
							continue;
						}

						$previousItems += $orderedItem->getCount();
					}
				}
				$limit -= $previousItems;
				$applicableCnt = floor($totalCount / $discountStep) - $previousItems;
				$applicableCnt = min($count, $applicableCnt);
				$applicableCnt = max(0, $applicableCnt);
			}
			else
			{
				$applicableCnt = 0;
			}
		}

		if (!is_null($limit))
		{
			$applicableCnt = min($limit, $applicableCnt);
			$applicableCnt = max(0, $applicableCnt);
		}

		$subTotal = ($applicableCnt * $discountPrice) + (($count - $applicableCnt) * $itemPrice);
		$item->setItemPrice($subTotal / $count);
	}

	protected function sortByPrice($a, $b)
	{
		$a = $a->getOriginalPrice();
		$b = $b->getOriginalPrice();

		if ($a == $b)
		{
			return 0;
		}
		else
		{
			return $a > $b ? 1 : -1;
		}
	}

	protected function getDiscountAmount($price)
	{
		return $price * ($this->getParam('amount', 0) / 100);
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