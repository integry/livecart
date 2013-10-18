<?php


/**
 * Search site news
 *
 * @package application/model/searchable
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
			}

	public function getSelectFilter($searchTerm)
	{
		$c = new ARExpressionHandle($this->getWeighedSearchCondition(array('name' => 1), $searchTerm));
		$f = new ARSelectFilter(new MoreThanCond($c, 0));
		$f->order($c, 'DESC');
		return $f;
	}
}

?>