<?php


/**
 * Search orders
 * 
 * @package application/model/searchable
 * @author Integry Systems
 */
class SearchableOrder extends SearchableModel
{
	public function getClassName()
	{
		return 'CustomerOrder';
	}

	public function loadClass()
	{
			}

	public function getSelectFilter($searchTerm)
	{
		$c = new ARExpressionHandle($this->getWeighedSearchCondition(array('invoiceNumber' => 1), $searchTerm));

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