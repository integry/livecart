<?php

ClassLoader::import('application.model.searchable.SearchableModel');

/**
 * Search site news
 *
 * @package application.model.searchable
 * @author Integry Systems
 */
class SearchableNewsPost extends SearchableModel
{
	protected $weightSearchConditionFields = array('title' => 4, 'text' => 2, 'moreText' => 1);
	
	public function getClassName()
	{
		return 'NewsPost';
	}

	public function loadClass()
	{
		ClassLoader::import('application.model.sitenews.NewsPost');
	}

	public function getSelectFilter($searchTerm)
	{
		$c = new ARExpressionHandle($this->getWeighedSearchCondition($this->weightSearchConditionFields , $searchTerm));
		
		$f = new ARSelectFilter(new MoreThanCond($c, 0));
		$f->setOrder($c, 'DESC');
		return $f;
	}
}

?>