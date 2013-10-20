<?php


/**
 * Search static pages
 *
 * @package application/model/searchable
 * @author Integry Systems
 */
class SearchableStaticPage extends SearchableModel
{
	

	public function getClassName()
	{
		return 'StaticPage';
	}

	public function loadClass()
	{
			}

	public function getSelectFilter($searchTerm)
	{
		$c = new ARExpressionHandle($this->getWeighedSearchCondition
			(
				$this->getOption('BACKEND_QUICK_SEARCH')
					? array('title' => 1)
					: array('title' => 2, 'text' => 1),
				$searchTerm
			));

		$f = new ARSelectFilter(new MoreThanCond($c, 0));
		$f->orderBy($c, 'DESC');
		return $f;
	}
}

?>