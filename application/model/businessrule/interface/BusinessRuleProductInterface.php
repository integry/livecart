<?php

/**
 * Implements setItemPrice and getItemPrice methods of OrderedItem
 *
 * @author Integry Systems
 * @package application.model.businessrule
 */
interface BusinessRuleProductInterface
{
	public function getPriceWithoutTax();
	public function setItemPrice($price);
	public function getCount();
	public function getProduct();
}

?>