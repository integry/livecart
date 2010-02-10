<?php

ClassLoader::import('library.activerecord.ARFeed');
ClassLoader::import('application.model.product.Product');

/**
 * Product data feed
 *
 * @author Integry Systems
 * @package application.controller
 */
class ProductFeed extends ARFeed
{
	protected $productFilter;

	public function __construct(ProductFilter $filter)
	{
		$this->productFilter = $filter;

		parent::__construct($filter->getSelectFilter(), 'Product', array('Category', 'ProductImage', 'Manufacturer'));
	}

	protected function postProcessData()
	{
		ProductPrice::loadPricesForRecordSetArray($this->data);
		ProductSpecification::loadSpecificationForRecordSetArray($this->data, true);
		Product::loadCategoryPathsForArray($this->data);
	}
}

?>
