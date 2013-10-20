<?php


/**
 *
 * @author Integry Systems
 * @package application/model/businessrule/condition
 */
class RuleConditionCurrencyIs extends RuleCondition
{
	public function isApplicable()
	{
		if (!($this->getorderBy() instanceof CustomerOrder))
		{
			return false;
		}

		$currencyID = $this->getorderBy()->getCurrency()->getID();
		$values = $this->getParam('serializedCondition', array('values' => array()));
		return !empty($values['values'][$currencyID]);
	}
}

?>
