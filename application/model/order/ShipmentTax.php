<?php

namespace order;

/**
 * Tax amount for a particular shipment. One shipment can have multiple taxes, depending on
 * how they are set up for a particular system.
 *
 * @package application/model/order
 * @author Integry Systems <http://integry.com>
 */
class ShipmentTax extends \ActiveRecordModel
{
	const TYPE_SUBTOTAL = 1;

	const TYPE_SHIPPING = 2;

	public $ID;
//	public $taxRateID;
//	public $shipmentID;
	public $type;
	public $amount;

	/**
	 * Create a new instance
	 *
	 * @return ShipmentTax
	 */
	public static function getNewInstance(TaxRate $taxRate, Shipment $shipment, $type)
	{
	  	$instance = ActiveRecordModel::getNewInstance(__CLASS__);
	  	$instance->taxRate = $taxRate;
	  	$instance->shipment = $shipment;
	  	$instance->type = $type;
	  	$instance->recalculateAmount(null);

	  	return $instance;
	}

	/**
	 * Recalculate tax amount
	 */
	public function recalculateAmount($recalculateShipping = true)
	{
		if (!$this->taxRate)
		{
			return $this->amount;
		}

		$shipment = $this->shipment;
		$tax = $this->taxRate->tax;
		$currency = $shipment->getCurrency();

		if ($recalculateShipping)
		{
			$shipment->recalculateAmounts(false);
		}

		$shipment->load();

		if (self::TYPE_SHIPPING == $this->type)
		{
			$totalAmount = $shipment->getShippingTotalBeforeTax();

			if (!$totalAmount)
			{
				$this->amount = 0;
				return;
			}

			$otherTaxes = 0;
			foreach ($this->shipment->getAppliedTaxes() as $appliedTax)
			{
				if (($this->type == $appliedTax->type))
				{
					if ($tax->includesTax($appliedTax->taxRate->tax))
					{
						$otherTaxes += $appliedTax->amount;
					}
				}
			}

			$totalAmount += $otherTaxes;
			$taxAmount = $this->taxRate->getTaxAmount($totalAmount);
		}
		else if (self::TYPE_SUBTOTAL == $this->type)
		{
			$taxAmount = 0;

			foreach ($shipment->getItems() as $item)
			{
				$class = $item->getProduct()->getTaxClass();
				if ($class !== $this->taxRate->taxClass)
				{
					continue;
				}

				$itemTotal = $item->getSubTotalBeforeTax();
				$otherTaxes = 0;
				foreach ($item->getTaxRates() as $taxRate)
				{
					if ($tax->includesTax($taxRate->tax))
					{
						$otherTaxes += $taxRate->getTaxAmount($itemTotal + $otherTaxes);
					}
				}

				$res = $this->taxRate->getTaxAmount($itemTotal + $otherTaxes);
				$taxAmount += $res;
			}
		}

		$this->amount = $taxAmount;
	}

	public function getAmount($amount = null)
	{
		return is_null($amount) ? $this->amount : $amount;
	}

	public function isItemTax()
	{
		return self::TYPE_SUBTOTAL == $this->type;
	}

	public function isShippingTax()
	{
		return self::TYPE_SHIPPING == $this->type;
	}

	public function toArray($amount = null)
	{
		$array = parent::toArray();
		$array['formattedAmount'] = array();

		$amountCurrency = $this->shipment->getCurrency();

		// get and format prices
		$array['formattedAmount'][$amountCurrency->getID()] = $amountCurrency->getFormattedPrice($this->getAmount($amount));

		if (!is_null($amount))
		{
			$array['amount'] = $amount;
		}

		return $array;
	}

	public function beforeCreate()
	{
		if (!$this->shipment->isExistingRecord())
		{
			return false;
		}


	}
}

?>
