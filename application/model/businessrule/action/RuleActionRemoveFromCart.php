<?php


/**
 *
 * @author Integry Systems
 * @package application.model.businessrule.action
 */
class RuleActionRemoveFromCart extends RuleAction implements RuleItemAction
{
	public function applyToItem(BusinessRuleProductInterface $item)
	{
		if ($item instanceof OrderedItem)
		{
			$item->customerOrder->get()->removeItem($item);
		}
	}
}

?>