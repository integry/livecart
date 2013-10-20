<?php


/**
 *
 * @author Integry Systems
 * @package application/model/businessrule/condition
 */
class RuleConditionOrderTotal extends RuleCondition implements RuleOrderCondition
{
	public function isApplicable()
	{
		return $this->compareValues($this->getorderBy()->getSubTotal(false), $this->params['subTotal']);
	}

	public static function getSortorderBy()
	{
		return 2;
	}
}

?>