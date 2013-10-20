<?php


/**
 *
 * @author Integry Systems
 * @package application/model/businessrule/condition
 */
class RuleConditionDeliveryZoneIs extends RuleCondition
{
	public function isApplicable()
	{
		if (!($this->getorderBy() instanceof CustomerOrder))
		{
			return false;
		}
		
		$zoneID = $this->getorderBy()->getDeliveryZone()->getID();
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
