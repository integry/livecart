<?php

ClassLoader::import("application.model.order.Shipment");
ClassLoader::import("application.model.tax.TaxRate");

/**
 * Tax amount for a particular shipment. One shipment can have multiple taxes, depending on
 * how they are set up for a particular system.
 *
 * @package application.model.order
 * @author Integry Systems <http://integry.com>
 */
class ShipmentTax extends ActiveRecordModel
{
	const TYPE_SUBTOTAL = 1;

	const TYPE_SHIPPING = 2;

	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName(__class__);

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("taxRateID", "TaxRate", "ID", "TaxRate", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("shipmentID", "Shipment", "ID", "Shipment", ARInteger::instance()));
		$schema->registerField(new ARField("type", ARInteger::instance(2)));
		$schema->registerField(new ARField("amount", ARFloat::instance()));
	}

	/**
	 * Create a new instance
	 *
	 * @return ShipmentTax
	 */
	public static function getNewInstance(TaxRate $taxRate, Shipment $shipment, $type)
	{
	  	$instance = ActiveRecordModel::getNewInstance(__CLASS__);
	  	$instance->taxRate->set($taxRate);
	  	$instance->shipment->set($shipment);
	  	$instance->type->set($type);
	  	$instance->recalculateAmount(null);

	  	return $instance;
	}

	/**
	 * Recalculate tax amount
	 */
	public function recalculateAmount($recalculateShipping = true)
	{
		if (!$this->taxRate->get())
		{
			return $this->amount->get();
		}

		$shipment = $this->shipment->get();
		$tax = $this->taxRate->get()->tax->get();
		$currency = $shipment->getCurrency();

		if ($recalculateShipping)
		{
			$shipment->recalculateAmounts(false);
		}

		$shipment->load();

		if (self::TYPE_SHIPPING == $this->type->get())
		{
			$totalAmount = $shipment->getShippingTotalBeforeTax();

			if (!$totalAmount)
			{
				$this->amount->set(0);
				return;
			}

			$otherTaxes = 0;
			foreach ($this->shipment->get()->getAppliedTaxes() as $appliedTax)
			{
				if (($this->type->get() == $appliedTax->type->get()))
				{
					if ($tax->includesTax($appliedTax->taxRate->get()->tax->get()))
					{
						$otherTaxes += $appliedTax->amount->get();
					}
				}
			}

			$totalAmount += $otherTaxes;
			$taxAmount = $this->taxRate->get()->getTaxAmount($totalAmount);
		}
		else if (self::TYPE_SUBTOTAL == $this->type->get())
		{
			$taxAmount = 0;

			foreach ($shipment->getItems() as $item)
			{
				$class = $item->getProduct()->getTaxClass();
				if ($class !== $this->taxRate->get()->taxClass->get())
				{
					continue;
				}

				$itemTotal = $item->getSubTotalBeforeTax();
				$otherTaxes = 0;
				foreach ($item->getTaxRates() as $taxRate)
				{
					if ($tax->includesTax($taxRate->tax->get()))
					{
						$otherTaxes += $taxRate->getTaxAmount($itemTotal + $otherTaxes);
					}
				}

				$res = $this->taxRate->get()->getTaxAmount($itemTotal + $otherTaxes);
				$taxAmount += $res;
			}
		}

		$this->amount->set($taxAmount);
	}

	public function getAmount($amount = null)
	{
		return is_null($amount) ? $this->amount->get() : $amount;
	}

	public function isItemTax()
	{
		return self::TYPE_SUBTOTAL == $this->type->get();
	}

	public function isShippingTax()
	{
		return self::TYPE_SHIPPING == $this->type->get();
	}

	public function toArray($amount = null)
	{
		$array = parent::toArray();
		$array['formattedAmount'] = array();

		$amountCurrency = $this->shipment->get()->getCurrency();

		// get and format prices
		$array['formattedAmount'][$amountCurrency->getID()] = $amountCurrency->getFormattedPrice($this->getAmount($amount));

		if (!is_null($amount))
		{
			$array['amount'] = $amount;
		}

		return $array;
	}

	protected function insert()
	{
		if (!$this->shipment->get()->isExistingRecord())
		{
			return false;
		}

		return parent::insert();
	}
}

?>