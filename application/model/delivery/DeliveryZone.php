<?php

namespace delivery;

use \order\Shipment;

require_once(__ROOT__ . '/library/shipping/ShippingRateSet.php');

/**
 * Delivery zones are used to classify shipping locations, which allows to define different
 * shipping rates and taxes for different delivery addresses.
 *
 * Delivery zone is determined automatically when user proceeds with the checkout and enters
 * the shipping address. In case no rules match for the shipping zone, the default delivery
 * zone is used.
 *
 * The delivery zone address rules can be set up in several ways - by assigning whole countries
 * or states or by defining mask strings, that allow to recognize addresses by city names, postal
 * codes or even street addresses.
 *
 * @package application/model/delivery
 * @author Integry Systems <http://integry.com>
 */
class DeliveryZone extends \ActiveRecordModel
{
	const BOTH_RATES = 0;
	const TAX_RATES = 1;
	const SHIPPING_RATES = 2;

	const ENABLED_TAXES = false;

	private $taxRates = null;

	public $ID;
	public $name;
	public $isEnabled;
	public $isFreeShipping;
	public $isRealTimeDisabled;
	public $type;

	public function initialize()
	{
		$this->hasMany('ID', 'tax\TaxRate', 'deliveryZoneID', array('alias' => 'TaxRates'));
		$this->hasMany('ID', 'delivery\ShippingService', 'deliveryZoneID', array('alias' => 'ShippingServices'));
	}

	/*####################  Static method implementations ####################*/

	public function isDefault()
	{
		return 1 == $this->getID();
	}

	public function isTaxIncludedInPrice()
	{
		return $this->isDefault() || $this->hasSameTaxRatesAsDefaultZone();
	}

	public function hasSameTaxRatesAsDefaultZone()
	{
		$defaultZoneRates = self::getDefaultZoneInstance()->taxRates;
		$ownRates = $this->taxRates;

		if (!$ownRates->count() || !$defaultZoneRates->count())
		{
			return false;
		}

		foreach ($ownRates as $rate)
		{
			foreach ($defaultZoneRates as $dzRate)
			{
				$found = false;
				if (($dzRate->tax == $rate->tax) && ($dzRate->taxClass == $rate->taxClass))
				{
					$found = true;
					if ($dzRate->rate != $rate->rate)
					{
						return false;
					}
				}

				if (!$found)
				{
					return false;
				}
			}
		}

		return true;
	}

	/*####################  Instance retrieval ####################*/

	/**
	 * @return ARSet
	 */
	public static function getAll($type = null)
	{
		$filter = new ARSelectFilter();

		if ($type)
		{
			$filter = new ARSelectFilter(new InCond(new ARFieldHandle('DeliveryZone','type'), array(DeliveryZone::BOTH_RATES, $type)));
		}

		return self::getRecordSet(__CLASS__, $filter);
	}

	/**
	 * @return ARSet
	 */
	public static function getEnabled()
	{
		$filter = new ARSelectFilter();
		$filter->setCondition(new EqualsCond(new ArFieldHandle(__CLASS__, "isEnabled"), 1));

		return self::getRecordSet(__CLASS__, $filter);
	}

	/**
	 * Returns the delivery zone, which matches the required address
	 *
	 * @return DeliveryZone
	 * @todo implement
	 */
	public static function getZoneByAddress(UserAddress $address, $type = 0)
	{
		$zones = self::getAllZonesByAddress($address, $type);
		return $zones ? array_shift($zones) : DeliveryZone::getDefaultZoneInstance();
	}

	public static function getTaxZones()
	{
		return self::getAll(self::TAX_RATES);
	}

	public static function getAllZonesByAddress(UserAddress $address, $type = 0)
	{
		$zones = array();
		// get zones by state
		if ($address->state)
		{
			$f = query::query()->where('DeliveryZone.isEnabled = :DeliveryZone.isEnabled:', array('DeliveryZone.isEnabled' => true));
			$f->andWhere('DeliveryZoneState.stateID = :DeliveryZoneState.stateID:', array('DeliveryZoneState.stateID' => $address->state->getID()));
			$f->andWhere(new InCond(new ARFieldHandle('DeliveryZone','type'), array(DeliveryZone::BOTH_RATES, $type)));
			$s = ActiveRecordModel::getRecordSet('DeliveryZoneState', $f, ActiveRecordModel::LOAD_REFERENCES);
			foreach ($s as $zoneState)
			{
				$zones[] = $zoneState->deliveryZone;
			}
		}

		// get zones by country
		if (!$zones)
		{
			$f = query::query()->where('DeliveryZone.isEnabled = :DeliveryZone.isEnabled:', array('DeliveryZone.isEnabled' => true));
			$f->andWhere('DeliveryZoneCountry.countryCode = :DeliveryZoneCountry.countryCode:', array('DeliveryZoneCountry.countryCode' => $address->countryID));
			$f->andWhere(new InCond(new ARFieldHandle('DeliveryZone','type'), array(DeliveryZone::BOTH_RATES, $type)));
			$s = ActiveRecordModel::getRecordSet('DeliveryZoneCountry', $f, array('DeliveryZone'));
			foreach ($s as $zone)
			{
				$zones[] = $zone->deliveryZone;
			}
		}

		$maskPoints = array();

		// leave zones that match masks
		foreach ($zones as $key => $zone)
		{
			$match = $zone->getMaskMatch($address);

			if (!$match)
			{
				unset($zones[$key]);
			}
			else
			{
				$maskPoints[$key] = $match;
			}
		}

		if ($maskPoints)
		{
			arsort($maskPoints);
			// this should really be a one-liner, but not today
			$ret = array();
			foreach (array_keys($maskPoints) as $key)
			{
				$ret[] = $zones[$key];
			}

			return $ret;
		}

		return $zones;
	}

	/**
	 * Returns the default delivery zone instance
	 *
	 * @return DeliveryZone
	 */
	public static function getDefaultZoneInstance()
	{
		return self::getInstanceById(1);
	}

	/*####################  Get related objects ####################*/

	/**
	 * Determine if the supplied UserAddress matches address masks
	 *
	 * @return bool
	 */
	public function getMaskMatch(UserAddress $address)
	{
		return
			   $this->hasMaskGroupMatch($this->getCityMasks(), $address->city) +

			   ($this->hasMaskGroupMatch($this->getAddressMasks(), $address->address1) ||
			   $this->hasMaskGroupMatch($this->getAddressMasks(), $address->address2)) +

			   $this->hasMaskGroupMatch($this->getZipMasks(), $address->postalCode);
	}

	private function hasMaskGroupMatch(ARSet $masks, $addressString)
	{
		if (!$masks->count())
		{
			return true;
		}

		$match = false;

		foreach ($masks as $mask)
		{
			if ($this->isMaskMatch($addressString, $mask->mask))
			{
				$match = 2;
			}
		}

		return $match;
	}

	private function isMaskMatch($addressString, $maskString)
	{
		$maskString = str_replace('*', '.*', $maskString);
		$maskString = str_replace('?', '.{0,1}', $maskString);
		$maskString = str_replace('/', '\/', $maskString);
		$maskString = str_replace('\\', '\\\\', $maskString);
		return @preg_match('/' . $maskString . '/im', $addressString);
	}

	/**
	 *  Returns manually defined shipping rates for the particular shipment
	 *
	 *	@return ShippingRateSet
	 */
	public function getDefinedShippingRates(Shipment $shipment)
	{
		$rates = new \ShippingRateSet();
		foreach ($this->getShippingServices() as $service)
		{
			$rate = $service->getDeliveryRate($shipment);
			if ($rate)
			{
				$rates->add($rate);
			}
		}

		if (!$shipment->getChargeableItemCount($this))
		{
			$app = self::getApplication();

			if ($app->getConfig()->get('FREE_SHIPPING_AUTO_RATE'))
			{
				$freeService = ShippingService::getNewInstance($this, $app->translate('_free_shipping'), ShippingService::WEIGHT_BASED);
				$freeRate = ShipmentDeliveryRate::getNewInstance($freeService, 0);
				$freeRate->setServiceID('FREE');
				$rates->add($freeRate);
			}
		}

		return $rates;
	}

	/**
	 *  Returns real time shipping rates for the particular shipment
	 *
	 *	@return ShippingRateSet
	 */
	public function getRealTimeRates(Shipment $shipment)
	{
		$rates = new ShippingRateSet();

		$app = self::getApplication();
		foreach ($app->getEnabledRealTimeShippingServices() as $handler)
		{
			$rates->merge(ShipmentDeliveryRate::getRealTimeRates($app->getShippingHandler($handler), $shipment));
		}

		return $rates;
	}

	/**
	 *  Returns both real time and calculated shipping rates for the particular shipment
	 *
	 *	@return ShippingRateSet
	 */
	public function getShippingRates(Shipment $shipment)
	{
		$defined = $this->getDefinedShippingRates($shipment);
		if (!$this->isRealTimeDisabled)
		{
			//$defined = array_merge($this->getRealTimeRates($shipment));
		}

		// calculate surcharge
		$surcharge = 0;
		foreach ($shipment->getItems() as $item)
		{
			$surcharge += ($item->getProduct()->getParent()->shippingSurchargeAmount * $item->getCount());
		}

		$currency = self::getApplication()->getDefaultCurrency();

		// apply to rates
		foreach ($defined as $rate)
		{
			$rate->setAmountByCurrency($currency, $rate->getAmountByCurrency($currency) + $surcharge);
		}

		// apply taxes
		foreach ($defined as $rate)
		{
			$zone = $shipment->getShippingTaxZone();
			$amount = !$zone->isDefault() ? $shipment->applyTaxesToShippingAmount($rate->getCostAmount()) : $rate->getCostAmount();
			$rate->setAmountWithTax($amount);
			$rate->setAmountWithoutTax($shipment->reduceTaxesFromShippingAmount($amount));
		}

		// look for "override" rates
		foreach ($defined as $rate)
		{
			if ($service = $rate->getService())
			{
				if ($service->isFinal)
				{
					$rates = new ShippingRateSet();
					$rates->add($rate);
					return $rates;
				}
			}
		}

		return $defined;
	}

	/**
	 * @return ARSet
	 */
	public function getCountries($loadReferencedRecords = false)
	{
		return DeliveryZoneCountry::getRecordSetByZone($this, $loadReferencedRecords);
	}

	/**
	 * @return ARSet
	 */
	public function getStates($loadReferencedRecords = array('State'))
	{
		return DeliveryZoneState::getRecordSetByZone($this, $loadReferencedRecords);
	}

	/**
	 * @return ARSet
	 */
	public function getCityMasks($loadReferencedRecords = false)
	{
		return DeliveryZoneCityMask::getRecordSetByZone($this, $loadReferencedRecords);
	}

	/**
	 * @return ARSet
	 */
	public function getZipMasks($loadReferencedRecords = false)
	{
		return DeliveryZoneZipMask::getRecordSetByZone($this, $loadReferencedRecords);
	}

	/**
	 * @return ARSet
	 */
	public function getAddressMasks($loadReferencedRecords = false)
	{
		return DeliveryZoneAddressMask::getRecordSetByZone($this, $loadReferencedRecords);
	}


	/**
	 * Get set of shipping sevices available in current zone
	 *
	 * @return ARSet
	 */
	public function getShippingServices()
	{
		return $this->shippingServices;
	}
}

?>
