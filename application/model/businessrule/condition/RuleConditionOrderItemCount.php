<?php

ClassLoader::import('application.model.businessrule.RuleCondition');

/**
 *
 * @author Integry Systems
 * @package application.model.businessrule.condition
 */
class RuleConditionOrderItemCount extends RuleCondition implements OrderCondition
{
	public function isApplicable()
	{
		return $this->compareValues($this->getContext()->getOrder()->getShoppingCartItemCount(), $this->params['count']);
	}
}

?>