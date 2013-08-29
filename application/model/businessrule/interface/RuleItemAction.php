<?php

/**
 *
 * @author Integry Systems
 * @package application/model/businessrule
 */
interface RuleItemAction
{
	public function applyToItem(BusinessRuleProductInterface $item);
}

?>