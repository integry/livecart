<?php

namespace tax;

/**
 * Defines a tax rate for a DeliveryZone. Tax rates are applied to order totals and shipping charges as well.
 *
 * @package application/model/tax
 * @author Integry Systems <http://integry.com>
 */
class TaxRate extends \ActiveRecordModel
{
	public $ID;
	public $rate;

	public function initialize()
	{
		$this->belongsTo('deliveryZoneID', 'delivery\DeliveryZone', 'ID', array('alias' => 'DeliveryZone'));
		$this->belongsTo('taxID', 'tax\Tax', 'ID', array('alias' => 'Tax'));
		$this->belongsTo('taxClassID', 'tax\TaxClass', 'ID', array('alias' => 'TaxClass'));
	}

	/**
	 * Create new tax rate
	 *
	 * @param DeliveryZone $deliveryZone Delivery zone instance
	 * @param Tax $tax Tax type
	 * @param float $rate Rate in percents
	 * @return TaxRate
	 */
	public static function getNewInstance(\delivery\DeliveryZone $deliveryZone, Tax $tax, $rate)
	{
	  	$instance = new self();
		$instance->deliveryZone = $deliveryZone;
	  	$instance->tax = $tax;
	  	$instance->rate = $rate;

	  	return $instance;
	}

	/**
	 * Load service rates record set
	 *
	 * @param ARSelectFilter $filter
	 * @param bool $loadReferencedRecords
	 *
	 * @return ARSet
	 */
	public static function getRecordSet(ARSelectFilter $filter, $loadReferencedRecords = false)
	{
		if (!$loadReferencedRecords)
		{
			$loadReferencedRecords = array('Tax');
		}
		$filter->orderBy('Tax.position');

		return parent::getRecordSet(__CLASS__, $filter, $loadReferencedRecords);
	}

	public function applyTax($amount)
	{
		return $amount + $this->getTaxAmount($amount);
	}

	public function getTaxAmount($amount)
	{
		return $amount * ($this->rate / 100);
	}

	public function getPosition()
	{
		$position = $this->tax->position * 10000;
		if ($class = $this->taxClass)
		{
			$position += $class->position + 1;
		}

		return $position;
	}
}

?>
