<?php

ClassLoader::import("application.model.report.Report");

/**
 * Generate customer data reports
 *
 * @package application.model.report
 * @author	Integry Systems
 */
class CustomerReport extends Report
{
	protected function getMainTable()
	{
		return 'User';
	}

	protected function getDateHandle()
	{
		return new ARFieldHandle('User', 'dateCreated');
	}

	public function getCustomerCounts()
	{
		$this->getData('COUNT(*)');
	}

	public function getCountries()
	{
		$this->setChartType(self::PIE);
		$q = $this->getQuery('COUNT(*)');
		$q->joinTable('BillingAddress', 'User', 'ID', 'defaultBillingAddressID');
		$q->joinTable('UserAddress', 'BillingAddress', 'ID', 'userAddressID');

		$f = $q->getFilter();
		$q->addField('countryID', null, 'entry');

		$handle = new ARFieldHandle('UserAddress', 'countryID');
		$f->setGrouping($handle);
		$f->mergeCondition(new NotEqualsCond($handle, ''));
		$f->mergeCondition(new IsNotNullCond($handle, ''));

		$this->getReportData($q);

		$info = $this->locale->info();
		foreach ($this->values['x'] as $value)
		{
			$value->label = $info->getCountryName($value->originalName) . ' (' . $value->value . ')';
		}
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