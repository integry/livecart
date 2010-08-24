<?php

ClassLoader::import('application.model.searchable.SearchableModel');

/**
 * Search user
 *
 * @package application.model.searchable
 * @author Integry Systems
 */
class SearchableUser extends SearchableModel
{
	public function getClassName()
	{
		return 'User';
	}

	public function loadClass()
	{
		ClassLoader::import('application.model.user.User');
	}

	public function getSelectFilter($searchTerm)
	{
		$c = new ARExpressionHandle($this->getWeighedSearchCondition(array('firstName' => 1,'lastName'=>1, 'email'=>1), $searchTerm));

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