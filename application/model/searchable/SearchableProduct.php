<?php

ClassLoader::import('application.model.searchable.SearchableModel');

/**
 * Search products
 *
 * @package application.model.searchable
 * @author Integry Systems
 */
class SearchableProduct extends SearchableModel
{
	public function getClassName()
	{
		return 'Product';
	}

	public function loadClass()
	{
		ClassLoader::import('application.model.product.Product');
	}

	public function getSelectFilter($searchTerm)
	{
		if (strpos($searchTerm,',') !== false)
		{
			$searchTerm = explode(',', $searchTerm);
			foreach($searchTerm as &$term)
			{
				$term = trim($term);
			}
			$searchTerm = array_filter($searchTerm);
		}
		$c = new ARExpressionHandle($this->getWeighedSearchCondition(array('name' => 1, 'sku'=>1), $searchTerm));
		$f = new ARSelectFilter(new MoreThanCond($c, 0));
		$f->setOrder($c, 'DESC');
		return $f;
	}

	public function isFrontend()
	{
		return false;
	}
}

?>