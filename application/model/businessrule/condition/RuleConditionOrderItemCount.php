<?php

ClassLoader::import('application/model/businessrule/RuleCondition');
ClassLoader::import('application/model/businessrule/interface/RuleOrderCondition');

/**
 *
 * @author Integry Systems
 * @package application/model/businessrule/condition
 */
class RuleConditionOrderItemCount extends RuleCondition implements RuleOrderCondition
{
	public function isApplicable()
	{
		return $this->compareValues($this->getorderBy()->getShoppingCartItemCount(), $this->params['count']);
	}

	public static function getSortorderBy()
	{
		return 3;
	}
}

?>