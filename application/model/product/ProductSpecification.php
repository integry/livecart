<?php

ClassLoader::import("application.model.product.Specification");

/**
 * Product specification wrapper class
 * Loads/modifies product specification data
 *
 * @author Saulius Rupainis <saulius@integry.net>
 * @package application.model.product
 */
class ProductSpecification implements IteratorAggregate
{
	private $product = null;

	public function __construct(Product $product)
	{
		$this->product = $product;
		$this->product->load();
	}

	public function setProperty()
	{

	}

	public function removeProperty()
	{

	}
}

?>