<?php

/**
 *
 * @author Integry Systems
 * @package application.model.businessrule
 */
interface RuleItemAction
{
	public function applyToItem(OrderedItem $item);
}

?>