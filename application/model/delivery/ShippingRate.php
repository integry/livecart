<?php

namespace delivery;

/**
 * Define rules for shipping cost calculation, which can be based on shipment weight, subtotal,
 * number of items, etc. Each ShippingRate entity defines one concrete shipping cost calculation
 * formula for a defined shipping weight or subtotal interval. ShippingRate's belong to ShippingService
 * entities.
 *
 * Shipping rate is being calculated as follows:
 *
 * weight based rates:
 *	 rate = flatCharge + (itemCount * perItemCharge) + (shipmentWeight * perKgCharge)
 *
 * subtotal based rates:
 *	 rate = flatCharge + (itemCount * perItemCharge) + (shipmentSubtotal * subtotalPercentCharge)
 *
 * @package application/model/delivery
 * @author Integry Systems <http://integry.com>
 */
class ShippingRate extends \ActiveRecordModel
{
	public $ID;
	public $weightRangeStart;
	public $weightRangeEnd;
	public $subtotalRangeStart;
	public $subtotalRangeEnd;
	public $flatCharge;
	public $perItemCharge;
	public $subtotalPercentCharge;
	public $perKgCharge;
	public $perItemChargeClass;

	public function initialize()
	{
		$this->belongsTo('shippingServiceID', 'delivery\ShippingService', 'ID', array('alias' => 'ShippingService'));
	}

	/*####################  Static method implementations ####################*/

	/**
	 * Create new shipping rate instance
	 *
	 * @param ShippingService $shippingService Shipping service instance
	 * @param float $rangeStart Lower range limit
	 * @param float $rangeEnd Higher range limit
	 * @return ShippingRate
	 */
	public static function getNewInstance(ShippingService $shippingService)
	{
	  	$instance = new self();
	  	$instance->shippingService = $shippingService;

	  	return $instance;
	}

	/*####################  Instance retrieval ####################*/

	/**
	 * Load service rates from known service
	 *
	 * @param ShippingService $service
	 * @param bool $loadReferencedRecords
	 *
	 * @return ARSet
	 */
	public static function getRecordSetByService(ShippingService $service, $loadReferencedRecords = false)
	{
 		$filter = new ARSelectFilter();

		$filter->setCondition(new EqualsCond(new ARFieldHandle(__CLASS__, "shippingServiceID"), $service->getID()));

		return self::getRecordSet($filter, $loadReferencedRecords);
	}

	/*####################  Value retrieval and manipulation ####################*/

	public function setRangeStart($rangeStart)
	{
		return ($this->getRangeType() == ShippingService::WEIGHT_BASED) ? $this->weightRangeStart = $rangeStart : $this->subtotalRangeStart = $rangeStart;
	}

	public function setRangeEnd($rangeEnd)
	{
		return ($this->getRangeType() == ShippingService::WEIGHT_BASED) ? $this->weightRangeEnd = $rangeEnd : $this->subtotalRangeEnd = $rangeEnd;
	}

	public function getRangeStart()
	{
		return ($this->getRangeType() == ShippingService::WEIGHT_BASED) ? $this->weightRangeStart : $this->subtotalRangeStart;
	}

	public function getRangeEnd()
	{
		return ($this->getRangeType() == ShippingService::WEIGHT_BASED) ? $this->weightRangeEnd : $this->subtotalRangeEnd;
	}

	public function getRangeType()
	{
		return $this->shippingService->rangeType;
	}

	public function setClassItemCharge(ShippingClass $class, $charge)
	{
		$this->setValueByLang('perItemChargeClass', $class->getID(), $charge);
	}

	/**
	 *  Get per item charge for particular item depending on its shipping class
	 */
	public function getItemCharge(\order\OrderedItem $item)
	{
		$product = $item->getProduct()->getParent();

		//$class = $product->shippingClass;
		// @todo: remove
		$class = null;
		if (!$class)
		{
			return $this->perItemCharge;
		}

		$charge = $this->getValueByLang('perItemChargeClass', $class->getID());
		if (is_null($charge) || !strlen($charge))
		{
			return $this->perItemCharge;
		}

		return $charge;
	}
}

?>
