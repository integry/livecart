<?php


/**
 * Generate sales reports
 *
 * @package application/model/report
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
}

?>