<?php

ClassLoader::import('application.model.businessrule.RuleCondition');

/**
 *
 * @author Integry Systems
 * @package application.model.businessrule.condition
 */
class RuleConditionCurrencyIs extends RuleCondition
{
	public function isApplicable()
	{
		if (!($this->getOrder() instanceof CustomerOrder))
		{
			return false;
		}

		$currencyID = $this->getOrder()->getCurrency()->getID();
		$values = $this->getParam('serializedCondition', array('values' => array()));
		return !empty($values['values'][$currencyID]);
	}
}

?>
