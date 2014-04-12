<?php

namespace delivery;

use order\Shipment;

/**
 * Pre-defined shipping service plan, that is assigned to a particular DeliveryZone.
 * Each ShippingService entity can contain several ShippingRate entities to determine
 * the actual shipping rates.
 *
 * In addition to pre-defined rates, it is also possible to use real-time shipping
 * rate calculation with integrated postal company web services.
 *
 * @package application/model/delivery
 * @author Integry Systems <http://integry.com>
 */
class ShippingService extends \system\MultilingualObject implements \eav\EavAble
{
	const WEIGHT_BASED = 0;
	const SUBTOTAL_BASED = 1;

	public $ID;
	public $isFinal;
	public $name;
	public $position;
	public $rangeType;
	public $description;
	public $deliveryTimeMinDays;
	public $deliveryTimeMaxDays;
	public $isLocalPickup;

	public function initialize()
	{
		$this->hasMany('ID', 'delivery\ShippingRate', 'shippingServiceID', array('alias' => 'ShippingRates'));
		$this->belongsTo('deliveryZoneID', 'delivery\DeliveryZone', 'ID', array('alias' => 'DeliveryZone'));
	}

	/*####################  Static method implementations ####################*/

	/**
	 * Create new shipping service
	 *
	 * @param DeliveryZone $deliveryZone Delivery zone
	 * @param string $defaultLanguageName Service name in default language
	 * @param integer $calculationCriteria Shipping price calculation criteria. 0 for weight based calculations, 1 for subtotal based calculations
	 * @return ShippingService
	 */
	public static function getNewInstance(DeliveryZone $deliveryZone)
	{
		$instance = new self();
		$instance->deliveryZone = $deliveryZone;

		return $instance;
	}

	/*####################  Get related objects ####################*/

	/**
	 * Get active record set from current service
	 *
	 * @param boolean $loadReferencedRecords
	 * @return ARSet
	 */
	public function getRates($loadReferencedRecords = false)
	{
		return ShippingRate::getRecordSetByService($this, $loadReferencedRecords);
	}

	/**
	 * Calculate a delivery rate for a particular shipment
	 *
	 * @return ShipmentDeliveryRate
	 */
	public function getDeliveryRate(Shipment $shipment)
	{
		$hasFreeShipping = false;

		$f = ShippingRate::query()->where('shippingServiceID = :shippingServiceID:', array('shippingServiceID' => $this->getID()));

		// get applicable rates
		if (self::WEIGHT_BASED == $this->rangeType)
		{
			$weight = $shipment->getChargeableWeight($this->deliveryZone);
			$f->andWhere('(weightRangeStart <= :weightRangeStart:) OR (weightRangeStart IS NULL)', array('weightRangeStart' => $weight * 1.000001));
			$f->andWhere('weightRangeEnd >= :weightRangeEnd:', array('weightRangeEnd' => $weight * 0.99999));
		}
		else
		{
			$total = $shipment->getSubTotal(Shipment::WITHOUT_TAXES);
			$f->andWhere('(subtotalRangeStart <= :subtotalRangeStart:) OR (subtotalRangeStart IS NULL)', array('subtotalRangeStart' => $total * 1.000001));
			$f->andWhere('subtotalRangeEnd >= :subtotalRangeEnd:', array('subtotalRangeEnd' => $total * 0.99999));
		}

		$rates = $f->execute();
		if (!$rates->count())
		{
			return null;
		}

		$itemCount = $shipment->getChargeableItemCount($this->deliveryZone);

		$maxRate = 0;

		foreach ($rates as $rate)
		{
			$charge = $rate->flatCharge;

			foreach ($shipment->getItems() as $item)
			{
				$charge += ($rate->getItemCharge($item) * $item->getCount());
			}

			if (self::WEIGHT_BASED == $this->rangeType)
			{
				$charge += ($rate->perKgCharge * $weight);
			}
			else
			{
				$charge += ($rate->subtotalPercentCharge / 100) * $total;
			}

			if ($charge > $maxRate)
			{
				$maxRate = $charge;
			}
		}

		return ShipmentDeliveryRate::getNewInstance($this, $maxRate);
	}

	public function deleteShippingRates()
	{
		foreach($this->getRates() as $rate)
		{
			$rate->delete();
		}
	}

    public function isLocalPickup()
    {
        return (boolean)$this->isLocalPickup;
    }

	public function toArray()
	{
		$this->getSpecification();
		return parent::toArray();
	}
}
?>
