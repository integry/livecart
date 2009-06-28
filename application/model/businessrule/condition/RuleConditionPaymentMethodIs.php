<?php

ClassLoader::import('application.model.businessrule.RuleCondition');

/**
 *
 * @author Integry Systems
 * @package application.model.businessrule.condition
 */
class RuleConditionDeliveryZoneIs extends RuleCondition
{
	public function isApplicable()
	{
		$method = $this->getContext()->getOrder()->getPaymentMethod();
		var_dump($this->params['values']);
	}
}

?>