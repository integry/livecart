<?php

ClassLoader::import('application.model.businessrule.RuleCondition');
ClassLoader::import('application.model.businessrule.interface.RuleOrderCondition');

/**
 *
 * @author Integry Systems
 * @package application.model.businessrule.condition
 */
class RuleConditionOrderItemCount extends RuleCondition implements RuleOrderCondition
{
	public function isApplicable()
	{
		return $this->compareValues($this->getOrder()->getShoppingCartItemCount(), $this->params['count']);
	}

	public static function getSortOrder()
	{
		return 3;
	}
}

?>