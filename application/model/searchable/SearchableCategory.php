<?php

ClassLoader::import('application.model.searchable.SearchableModel');

/**
 * Search products
 *
 * @package application.model.searchable
 * @author Integry Systems
 */
class SearchableCategory extends SearchableModel
{
	public function getClassName()
	{
		return 'Category';
	}

	public function loadClass()
	{
		ClassLoader::import('application.model.category.Category');
	}

	public function getSelectFilter($searchTerm)
	{
		$c = new ARExpressionHandle($this->getWeighedSearchCondition(array('name' => 1), $searchTerm));

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