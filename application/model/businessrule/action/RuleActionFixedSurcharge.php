<?php

ClassLoader::import('application.model.businessrule.action.RuleActionFixedDiscount');

/**
 *
 * @author Integry Systems
 * @package application.model.businessrule.action
 */
class RuleActionFixedSurcharge extends RuleActionFixedDiscount
{
	protected function getDiscountAmount($price)
	{
		return parent::getDiscountAmount($price) * -1;
	}

	public static function getSortOrder()
	{
		return 4;
	}
}

?>