<?php

ClassLoader::import('application.model.businessrule.interface.BusinessRuleProductInterface');
ClassLoader::import('application.model.businessrule.RuleProductContainer');
ClassLoader::import('application.model.businessrule.interface.BusinessRuleOrderInterface');

/**
 * Implements setItemPrice and getItemPrice methods of OrderedItem
 *
 * @author Integry Systems
 * @package application.model.businessrule
 */
class RuleOrderContainer implements BusinessRuleOrderInterface
{
	private $total;
	private $currency;
	private $dateCompleted;
	private $items = array();

	public function __construct($dbData)
	{
		if (empty($dbData[0]['CustomerOrder']['dateCompleted']))
		{
			//return;
		}

		$this->currency = $dbData[0]['CustomerOrder']['currencyID'];
		$this->dateCompleted = $dbData[0]['CustomerOrder']['dateCompleted'];

		foreach ($dbData as $item)
		{
			$product = $item['Product'];
			if (!empty($product['Parent']))
			{
				$product = $product['Parent'];
			}

			// only keep the essential fields
			$orderItem = RuleProductContainer::createFromArray($product);
			$orderItem->setCount($item['count']);
			$orderItem->setItemPrice($item['price']);
			$this->items[] = $orderItem;
		}
	}

	/**
	 * 	@return RuleProductContainer[]
	 **/
	public function getPurchasedItems()
	{
		return $this->items;
	}

	public function getShoppingCartItems()
	{
		return $this->getPurchasedItems();
	}

	public function getShoppingCartItemCount()
	{
		$count = 0;
		foreach ($this->getPurchasedItems() as $item)
		{
			$count += $item->getCount();
		}

		return $count;
	}

	public function getCompletionDate()
	{
		return $this->dateCompleted;
	}

	public function setTotal($total)
	{
		$this->total = $total;
	}

	public function getTotal()
	{
		return $this->total;
	}

	public function getSubTotal()
	{
		return $this->getTotal();
	}

	public function getPaymentMethod()
	{
		return '';
	}

	public function setCoupons(ARSet $coupons)
	{
		$this->coupons = array();
		foreach ($coupons as $coupon)
		{
			$this->coupons[$coupon->couponCode->get()] = true;
		}
	}

	public function hasCoupon($code)
	{
		return !empty($this->coupons[$code]);
	}

	public function getCurrency()
	{
		return Currency::getInstanceById($this->currency);
	}
}

?>