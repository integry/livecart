<?php

ClassLoader::import('application.model.businessrule.RuleCondition');

/**
 * Main condition constraints - check if enabled, date interval, order coupons
 *
 * @author Integry Systems
 * @package application.model.businessrule.condition
 */
class RuleConditionRoot extends RuleCondition
{
	public function isApplicable()
	{
		if (empty($this->params['isEnabled']))
		{
			return false;
		}

		if (!empty($this->params['couponCode']))
		{
			if (!$this->getContext()->getOrder() || !$this->getContext()->getOrder()->hasCoupon($this->params['couponCode']))
			{
				return false;
			}
		}

		if (!empty($this->params['validFrom']))
		{
			if (strtotime($this->params['validFrom']) > time())
			{
				return false;
			}
		}

		if (!empty($this->params['validTo']))
		{
			if (strtotime($this->params['validTo']) < time())
			{
				return false;
			}
		}

		return true;
	}
}

?>