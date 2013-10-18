<?php


/**
 * Generate customer data reports
 *
 * @package application/model/report
 * @author	Integry Systems
 */
class SearchReport extends Report
{
	const TABLE_LIMIT = 100;

	protected function getMainTable()
	{
		return 'SearchLog';
	}

	protected function getDateHandle()
	{
		return new ARFieldHandle('SearchLog', 'time');
	}

	public function getTopSearches()
	{
		$this->setChartType(self::TABLE);
		$q = $this->getQuery('COUNT(*)');

		$f = $q->getFilter();
		$f->reorder();
		$f->resetGrouping();
		$f->order(new ARExpressionHandle('cnt'), 'DESC');
		$q->addField('keywords');
		$f->setGrouping(new ARExpressionHandle('keywords'));
		$f->limit(self::TABLE_LIMIT);

		$this->getReportData($q);

		$fields = array_flip(array('keywords', 'cnt'));
		$values = array();
		foreach ($this->values as $log)
		{
			$values[] = array_merge($fields, array_intersect_key($log, $fields));
		}

		$this->values = $values;
	}

	public function getOrderedItemCounts()
	{
		$q = $this->getQuery('COUNT(OrderedItem.ID)');
		$q->joinTable('OrderedItem', 'CustomerOrder', 'customerOrderID', 'ID');
		$this->getReportData($q);
	}

	public function getAvgOrderTotals()
	{
		$this->getData('ROUND(SUM(totalAmount * ' . $this->getCurrencyMultiplier() . ') / COUNT(dateCompleted), 2)');
	}

	public function getAvgItemCounts()
	{
		$this->getData('ROUND(SUM((SELECT COUNT(OrderedItem.ID) * OrderedItem.count FROM OrderedItem WHERE OrderedItem.customerOrderID=CustomerOrder.ID)) / COUNT(dateCompleted), 2)');
	}
}

?>