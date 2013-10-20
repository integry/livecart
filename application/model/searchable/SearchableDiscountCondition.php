<?php


/**
 * Search orders
 * 
 * @package application/model/searchable
 * @author Integry Systems
 */
class SearchableDiscountCondition extends SearchableModel
{
	public function getClassName()
	{
		return 'DiscountCondition';
	}

	public function loadClass()
	{
			}

	public function getSelectFilter($searchTerm)
	{
		$c = new ARExpressionHandle($this->getWeighedSearchCondition(array('name' => 1), $searchTerm));

		$f = new ARSelectFilter(new MoreThanCond($c, 0));
		$f->orderBy($c, 'DESC');
		return $f;
	}

	public function isFrontend()
	{
		return false;
	}
}

?>