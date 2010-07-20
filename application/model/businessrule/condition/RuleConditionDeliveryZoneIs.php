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
		if (!($this->getOrder() instanceof CustomerOrder))
		{
			return false;
		}
		
		$zoneID = $this->getOrder()->getDeliveryZone()->getID();
		foreach ($this->records as $record)
		{
			if ($record['ID'] == $zoneID)
			{
				return true;
			}
		}
	}
}

?>
