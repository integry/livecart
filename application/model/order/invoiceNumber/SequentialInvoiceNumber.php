<?php


/**
 * Sequential invoice numbers
 *
 * @package application.model.order
 * @author Integry Systems <http://integry.com>
 */
class SequentialInvoiceNumber extends InvoiceNumberGenerator
{
	private $selectFilter;

	public function getNumber()
	{
		$config = ActiveRecordModel::getApplication()->getConfig();
		$startAt = $config->get('SequentialInvoiceNumber_START_AT');
		$prefix = $config->get('SequentialInvoiceNumber_PREFIX');
		$suffix = $config->get('SequentialInvoiceNumber_SUFFIX');

		// get last finalized order
		$last = array_pop(ActiveRecord::getRecordSetArray('CustomerOrder', $this->getSelectFilter()));

		$lastNumber = $last ? $last['invoiceNumber'] : $startAt;

		if (substr($lastNumber, 0, strlen($prefix)) == $prefix)
		{
			$lastNumber = substr($lastNumber, strlen($prefix));
		}

		if (substr($lastNumber, -1 * strlen($suffix)) == $suffix)
		{
			$lastNumber = substr($lastNumber, 0, -1 * strlen($suffix));
		}

		preg_match('/[0-9]+/', $lastNumber, $matches);
		$lastNumber = array_shift($matches);

		if ($lastNumber < $startAt)
		{
			$lastNumber = $startAt;
		}

		// avoid selecting the same order if the invoice number is already taken
		$this->getSelectFilter()->mergeCondition(neq('CustomerOrder.ID', $last['ID']));

		$lastNumber += max($config->get('SequentialInvoiceNumber_STEP'), 1);
		$lastNumber = str_pad($lastNumber, $config->get('SequentialInvoiceNumber_MIN_LENGTH'), '0', STR_PAD_LEFT);

		$lastNumber = $prefix . $lastNumber . $suffix;

		return $lastNumber;
	}

	protected function getSelectFilter()
	{
		if (!$this->selectFilter)
		{
			$this->selectFilter = select(eq('CustomerOrder.isFinalized', true), isnotnull('CustomerOrder.invoiceNumber'));
			$this->selectFilter->setOrder(f('CustomerOrder.dateCompleted'), 'DESC');
			$this->selectFilter->setLimit(1);
		}

		return $this->selectFilter;
	}
}

?>