<?php


/**
 * Generate customer data reports
 *
 * @package application/model/report
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

	public function getTopCustomers()
	{
		$this->setDateHandle(new ARFieldHandle('CustomerOrder', 'dateCompleted'));
		$this->setChartType(self::TABLE);
		$q = $this->getQuery('ROUND(SUM(CustomerOrder.totalAmount * ' . $this->getCurrencyMultiplier() . '), 2)');

		$f = $q->getFilter();
		$f->reorderBy();
		$f->resetGrouping();
		$f->orderBy(new ARExpressionHandle('cnt'), 'DESC');
		$q->addField('userID');
		$f->setGrouping(new ARExpressionHandle('userID'));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isFinalized'), 1));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isCancelled'), 0));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isPaid'), 1));
		$f->limit(self::TABLE_LIMIT);
		$q->joinTable('CustomerOrder', 'User', 'userID', 'ID');

		$this->getReportData($q);

		$ids = array();
		foreach ($this->values as $product)
		{
			$ids[$product['userID']] = $product['cnt'];
		}

		// fetch user details
		$fields = array_flip(array('fullName', 'cnt'));
		foreach (ActiveRecordModel::getRecordSetArray('User', new ARSelectFilter(new INCond(new ARFieldHandle('User', 'ID'), array_keys($ids)))) as $user)
		{
			$user['cnt'] = $ids[$user['ID']];
			$ids[$user['ID']] = array_merge($fields, array_intersect_key($user, $fields));
		}

		$this->values = $ids;
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