<?php

ClassLoader::import('application.model.businessrule.RuleCondition');

/**
 *
 * @author Integry Systems
 * @package application.model.businessrule.condition
 */
class RuleConditionOrderTotal extends RuleCondition implements OrderCondition
{
	public function isApplicable()
	{
		return $this->compareValues($this->order->getSubTotal(false), $this->params['subTotal']);
	}
}

?>