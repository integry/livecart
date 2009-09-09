<?php

ClassLoader::import('application.model.order.CustomerOrder');
ClassLoader::import('application.model.user.User');
ClassLoader::import('application.model.businessrule.RuleProductContainer');

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

	private $products = array();

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

	public function getProducts()
	{
		return $this->products;
	}

	public function resetProducts()
	{
		$this->products = array();
	}

	public function addProduct($product)
	{
		static $calls = 0;

		$item = new RuleProductContainer($product);
		$this->products[] = $item;
		return $item;
	}

	public function removeLastProduct()
	{
		return array_pop($this->products);
	}

	public function addProducts(array $products)
	{
		foreach ($products as $product)
		{
			$this->addProduct($product);
		}
	}
}

?>