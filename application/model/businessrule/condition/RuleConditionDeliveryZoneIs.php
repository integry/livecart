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
		die(__CLASS__ . ' not implemented');
		return new EqualsCond(new ARFieldHandle('DiscountConditionRecord', 'deliveryZoneID'), $order->getDeliveryZone()->getID());
	}
}

?>