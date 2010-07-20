<?php

ClassLoader::import('application.model.businessrule.RuleAction');

/**
 *
 * @author Integry Systems
 * @package application.model.businessrule.action
 */
class RuleActionSetQuantity extends RuleAction implements RuleItemAction
{
	public function applyToItem(BusinessRuleProductInterface $item)
	{
		if ($item instanceof OrderedItem)
		{
			$item->count->set($this->getFieldValue('count'));
			$item->save();
		}
	}

	public function getFields()
	{
		return array(array('type' => 'number', 'label' => '_quantity', 'name' => 'count'));
	}
}

?>