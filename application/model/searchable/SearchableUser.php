<?php


/**
 * Search user
 *
 * @package application/model/searchable
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
			}

	public function getSelectFilter($searchTerm)
	{
		$c = new ARExpressionHandle($this->getWeighedSearchCondition(array('firstName' => 1,'lastName'=>1, 'email'=>1), $searchTerm));

		$f = new ARSelectFilter(new MoreThanCond($c, 0));
		$f->order($c, 'DESC');
		return $f;
	}

	public function isFrontend()
	{
		return false;
	}
}

?>