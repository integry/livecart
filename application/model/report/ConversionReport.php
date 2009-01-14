<?php

ClassLoader::import("application.model.report.Report");

/**
 * Generate sales reports
 *
 * @package application.model.report
 * @author	Integry Systems
 */
class ConversionReport extends Report
{
	protected function getMainTable()
	{
		return 'CustomerOrder';
	}

	protected function getDateHandle()
	{
		return new ARFieldHandle('CustomerOrder', 'dateCreated');
	}

	public function getConversionRatio()
	{
		$this->getData('ROUND((SUM(isFinalized) / COUNT(*)) * 100, 2)');
	}

	public function getCheckoutSteps()
	{
		$this->setChartType(self::PIE);
		$q = $this->getQuery('COUNT(*)');

		$f = $q->getFilter();
		$q->addField('checkoutStep', null, 'entry');
		$f->setGrouping(new ARExpressionHandle('entry'));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isFinalized'), 0));

		$this->getReportData($q);

		foreach ($this->values['x'] as $value)
		{
			$value->label = $this->application->translate('_progress_' . $value->originalName) . ' (' . $value->value . ')';
		}
	}

	public function getCartCounts()
	{
		$this->getData('COUNT(*)');
	}

	public function getStatuses()
	{
		$this->setChartType(self::PIE);
		$q = $this->getQuery('COUNT(*)');

		$f = $q->getFilter();
		$q->addField('IF (isCancelled = 1, -2, IF (isPaid = 0, -1, IF (status IS NULL, 0, status)))', null, 'entry');
		$f->setGrouping(new ARExpressionHandle('entry'));

		$this->getReportData($q);

		foreach ($this->values['x'] as $value)
		{
			$value->label = $this->application->translate(CustomerOrder::getStatusName($value->originalName));
		}
	}

	public function getCancelledRatio()
	{
		$this->getData('ROUND((SUM(isCancelled)/COUNT(*)) * 100, 2)');
	}

	public function getUnpaidRatio()
	{
		$this->getData('ROUND(((COUNT(*) - SUM(isPaid))/COUNT(*)) * 100, 2)');
	}
}

?>