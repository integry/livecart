<?php


/**
 *
 * @author Integry Systems
 * @package application.model.businessrule.condition
 */
class RuleConditionPaymentMethodIs extends RuleCondition
{
	public function isApplicable()
	{
		if (!$this->getOrder())
		{
			return;
		}

		$method = $this->getOrder()->getPaymentMethod();
		$values = $this->getParam('serializedCondition', array('values' => array()));
		return !empty($values['values'][$method]);
	}
}

?>