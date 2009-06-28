<?php

ClassLoader::import('application.model.businessrule.action.RuleActionPercentageDiscount');

/**
 *
 * @author Integry Systems
 * @package application.model.businessrule.action
 */
class RuleActionPercentageSurcharge extends RuleActionPercentageDiscount
{
	protected function getDiscountAmount($price)
	{
		return parent::getDiscountAmount($price) * -1;
	}
}

?>