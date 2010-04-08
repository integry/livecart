<?php

ClassLoader::import('application.model.searchable.SearchableModel');

/**
 * Search site news
 *
 * @package application.model.searchable
 * @author Integry Systems
 */
class SearchableManufacturer extends SearchableModel
{
	public function getClassName()
	{
		return 'Manufacturer';
	}

	public function loadClass()
	{
		ClassLoader::import('application.model.product.Manufacturer');
	}

	public function getSelectFilter($searchTerm)
	{
		$c = new ARExpressionHandle($this->getWeighedSearchCondition(array('name' => 1), $searchTerm));
		$f = new ARSelectFilter(new MoreThanCond($c, 0));
		$f->setOrder($c, 'DESC');
		return $f;
	}
}

?>