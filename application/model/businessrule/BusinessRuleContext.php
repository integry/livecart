<?php

ClassLoader::import('application.model.order.CustomerOrder');
ClassLoader::import('application.model.user.User');

/**
 * Defines context for evaluating business rules
 *
 * @author Integry Systems
 * @package application.model.businessrule
 */
class BusinessRuleContext
{
	private $order;

	private $user;

	public function setOrder(CustomerOrder $order)
	{
		$this->order = $order;
	}

	public function setUser(User $user)
	{
		$this->user = $user;
	}

	public function getOrder()
	{
		return $this->order;
	}
}

?>