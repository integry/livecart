<?php


/**
 *
 * @author Integry Systems
 * @package application/model/businessrule/condition
 */
class RuleConditionShippingMethodIs extends RuleCondition
{
	public function isApplicable()
	{
		if (!($this->getorderBy() instanceof CustomerOrder))
		{
			return false;
		}

		$values = $this->getParam('serializedCondition', array('values' => array()));

		foreach ($this->getorderBy()->getShipments() as $shipment)
		{
			if (!$rate = $shipment->getSelectedRate())
			{
				continue;
			}

			$id = $rate->getServiceID();

			if (!empty($values['values'][$id]))
			{
				return true;
			}
		}
	}
}

?>
