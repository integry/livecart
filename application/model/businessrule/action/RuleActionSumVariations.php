<?php


/**
 *
 * @author Integry Systems
 * @package application.model.businessrule.action
 */
class RuleActionSumVariations extends RuleAction implements RuleItemAction
{
	public function applyToItem(BusinessRuleProductInterface $item)
	{
		if ($item instanceof OrderedItem)
		{
			$item->setSumVariationDiscounts(true);
		}
	}
}

?>