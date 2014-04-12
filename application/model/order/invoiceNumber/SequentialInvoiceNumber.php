<?php

namespace order\invoiceNumber;

use \order\CustomerOrder;

/**
 * Sequential invoice numbers
 *
 * @package application/model/order
 * @author Integry Systems <http://integry.com>
 */
class SequentialInvoiceNumber extends \order\InvoiceNumberGenerator
{
	private $selectFilter;

	public function getNumber()
	{
		$config = $this->getConfig();
		$startAt = $config->get('SequentialInvoiceNumber_START_AT');
		$prefix = $config->get('SequentialInvoiceNumber_PREFIX');
		$suffix = $config->get('SequentialInvoiceNumber_SUFFIX');

		// get last finalized order
		$last = $this->getSelectFilter()->execute()->getFirst();

		$lastNumber = $last ? $last->invoiceNumber : $startAt;

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
		/*
		$this->getSelectFilter()->andWhere('ID != :id:', array('id' => $last['ID']));

		$lastNumber += max($config->get('SequentialInvoiceNumber_STEP'), 1);
		$lastNumber = str_pad($lastNumber, $config->get('SequentialInvoiceNumber_MIN_LENGTH'), '0', STR_PAD_LEFT);

		$lastNumber = $prefix . $lastNumber . $suffix;
		*/

		return $lastNumber;
	}

	protected function getSelectFilter()
	{
		$query = CustomerOrder::query()
					->andWhere('isFinalized = true AND invoiceNumber IS NOT NULL')
					->orderBy('dateCompleted DESC')
					->limit(1);

		return $query;
	}
}

?>
