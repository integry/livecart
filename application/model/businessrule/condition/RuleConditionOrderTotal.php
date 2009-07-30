<?php

ClassLoader::import('application.model.businessrule.RuleCondition');

/**
 *
 * @author Integry Systems
 * @package application.model.businessrule.condition
 */
class RuleConditionOrderTotal extends RuleCondition implements RuleOrderCondition
{
	public function isApplicable()
	{
		return $this->compareValues($this->getOrder()->getSubTotal(false), $this->params['subTotal']);
	}

	public static function getSortOrder()
	{
		return 2;
	}
}

?>