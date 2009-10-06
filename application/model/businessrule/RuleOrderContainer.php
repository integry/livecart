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

	public function getCompletionDate()
	{
		return $this->dateCompleted;
	}

	public function getTotal()
	{
		return $this->total;
	}

	public function getCurrency()
	{
		return Currency::getInstanceById($this->currency);
	}
}

?>