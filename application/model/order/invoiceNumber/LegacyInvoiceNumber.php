<?php

namespace order\invoiceNumber;

/**
 * Legacy invoice numbers (pre 1.3.0)
 * Equal to order database ID's, so they're not guaranteed to be even sequential
 *
 * @package application/model/order
 * @author Integry Systems <http://integry.com>
 */
class LegacyInvoiceNumber extends \order\InvoiceNumberGenerator
{
	private $selectFilter;

	public function getNumber()
	{
		return $this->order->getID();
	}
}

?>
