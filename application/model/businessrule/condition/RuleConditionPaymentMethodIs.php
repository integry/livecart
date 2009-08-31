<?php

ClassLoader::import('application.model.businessrule.RuleCondition');

/**
 *
 * @author Integry Systems
 * @package application.model.businessrule.condition
 */
class RuleConditionPaymentMethodIs extends RuleCondition
{
	public function isApplicable()
	{
		$method = $this->getContext()->getOrder()->getPaymentMethod();
		$values = $this->getParam('serializedCondition', array('values' => array()));
		return !empty($values['values'][$method]);
	}
}

?>