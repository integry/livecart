<?php

/**
 *
 * @author Integry Systems
 * @package application/model/businessrule
 */
interface RuleOrderAction
{
	public function applyToorderBy(CustomerOrder $order);
}

?>