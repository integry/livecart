<?php

ClassLoader::import('application.model.businessrule.RuleAction');

/**
 *
 * @author Integry Systems
 * @package application.model.businessrule.action
 */
class RuleActionIncludePastOrdersInQuantityPrices extends RuleAction implements RuleItemAction
{
	public function applyToItem(BusinessRuleProductInterface $item)
	{
		if ($item instanceof OrderedItem)
		{
			$item->setPastOrdersInQuantityPrices($this->getFieldValue('days'));
		}
	}

	public function getFields()
	{
		return array(array('type' => 'number', 'label' => '_orders_from_past_x_days', 'name' => 'days'));
	}
}

?>