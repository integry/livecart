<?php

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
class ShippingService extends MultilingualObject implements EavAble
{
	const WEIGHT_BASED = 0;
	const SUBTOTAL_BASED = 1;

	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("ShippingService");

		public $ID;
		public $deliveryZoneID", "DeliveryZone", "ID", "DeliveryZone;
		public $isFinal;
		public $name;
		public $position;
		public $rangeType;
		public $description;
		public $deliveryTimeMinDays;
		public $deliveryTimeMaxDays;
		public $eavObjectID", "eavObject", "ID", 'EavObject', ARInteger::instance()), false);
        public $isLocalPickup;
	}

	/*####################  Static method implementations ####################*/

	/**
	 * Gets an existing record instance
	 *
	 * @param mixed $recordID
	 * @param bool $loadRecordData
	 * @param bool $loadReferencedRecords
	 * @param array $data	Record data array (may include referenced record data)
	 *
	 * @return ShippingService
	 */
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false, $data = array())
	{
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords, $data);
	}

	/**
	 * Create new shipping service
	 *
	 * @param DeliveryZone $deliveryZone Delivery zone
	 * @param string $defaultLanguageName Service name in default language
	 * @param integer $calculationCriteria Shipping price calculation criteria. 0 for weight based calculations, 1 for subtotal based calculations
	 * @return ShippingService
	 */
	public static function getNewInstance(DeliveryZone $deliveryZone = null, $defaultLanguageName, $calculationCriteria)
	{
		$instance = new self();
		if($deliveryZone)
		{
			$instance->deliveryZone = $deliveryZone;
		}
		$instance->setValueByLang('name', null, $defaultLanguageName);
		$instance->rangeType = $calculationCriteria;

		return $instance;
	}

	/**
	 * Load delivery services record set
	 *
	 * @param ARSelectFilter $filter
	 * @param bool $loadReferencedRecords
	 *
	 * @return ARSet
	 */
	public static function getRecordSet(ARSelectFilter $filter, $loadReferencedRecords = false)
	{
		return parent::getRecordSet(__CLASS__, $filter, $loadReferencedRecords);
	}

	/*####################  Instance retrieval ####################*/

	/**
	 * Load delivery services record by Delivery zone
	 *
	 * @param DeliveryZone $deliveryZone
	 * @param bool $loadReferencedRecords
	 *
	 * @return ARSet
	 */
	public static function getByDeliveryZone(DeliveryZone $deliveryZone = null, $loadReferencedRecords = false)
	{
 		$filter = new ARSelectFilter();

		$filter->orderBy(new ARFieldHandle(__CLASS__, "position"), 'ASC');

		if (!$deliveryZone)
		{
			$deliveryZone = DeliveryZone::getDefaultZoneInstance();
		}

		if ($deliveryZone->isDefault())
		{
			$filter->setCondition(new IsNullCond(new ARFieldHandle(__CLASS__, "deliveryZoneID")));
		}
		else
		{
			$filter->setCondition(new EqualsCond(new ARFieldHandle(__CLASS__, "deliveryZoneID"), $deliveryZone->getID()));
		}

		$services = self::getRecordSet($filter, $loadReferencedRecords);

		if ($deliveryZone->isDefault())
		{
			foreach ($services as $service)
			{
				$service->deliveryZone = $deliveryZone;
			}
		}

		return $services;
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

		// get applicable rates
		if (self::WEIGHT_BASED == $this->rangeType)
		{
			$weight = $shipment->getChargeableWeight($this->deliveryZone);
			$cond = new EqualsOrLessCond('ShippingRate.weightRangeStart', $weight * 1.000001);
			$cond->andWhere(new EqualsOrMoreCond('ShippingRate.weightRangeEnd', $weight * 0.99999));
		}
		else
		{
			$total = $shipment->getSubTotal(Shipment::WITHOUT_TAXES);
			$cond = new EqualsOrLessCond('ShippingRate.subtotalRangeStart', $total * 1.000001);
			$cond->andWhere(new EqualsOrMoreCond('ShippingRate.subtotalRangeEnd', $total * 0.99999));
		}

		$f = query::query()->where('ShippingRate.shippingServiceID = :ShippingRate.shippingServiceID:', array('ShippingRate.shippingServiceID' => $this->getID()));
		$f->andWhere($cond);

		$rates = ActiveRecordModel::getRecordSet('ShippingRate', $f);

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

	public function save($forceOperation = null)
	{
		if ($this->deliveryZone && (0 == $this->deliveryZone->getID()))
		{
			$this->deliveryZone = null);
		}

		return parent::save($forceOperation);
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