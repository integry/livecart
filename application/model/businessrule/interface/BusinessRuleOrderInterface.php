<?php

/**
 * Implements methods of CustomerOrder
 *
 * @author Integry Systems
 * @package application.model.businessrule
 */
interface BusinessRuleOrderInterface
{
	public function getPurchasedItems();
	public function getCompletionDate();
	public function getTotal();
	public function getCurrency();
}

?>