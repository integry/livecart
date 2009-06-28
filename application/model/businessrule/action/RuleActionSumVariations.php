<?php

ClassLoader::import('application.model.businessrule.RuleAction');

/**
 *
 * @author Integry Systems
 * @package application.model.businessrule.action
 */
class RuleActionSumVariations extends RuleAction implements RuleItemAction
{
	public function applyToItem(OrderedItem $item)
	{
		$item->setSumVariationDiscounts(true);
	}
}

?>