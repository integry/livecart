<?php

namespace order;

/**
 * Represents a collection of ordered items that are shipped in the same package
 *
 * @package application/model/order
 * @author Integry Systems <http://integry.com>
 */
class Shipment extends \ActiveRecordModel
{
	public $items = array();

	/**
	 *  Used only for serialization
	 */
	protected $itemIds = array();

	protected $availableShippingRates = array();

	protected $selectedRateId;

	protected $fixedTaxes = array();

	private $deliveryZones = array();
	private $taxZones = array();

	const STATUS_NEW = 0;
	const STATUS_PROCESSING = 1;
	const STATUS_AWAITING = 2;
	const STATUS_SHIPPED = 3;
	const STATUS_RETURNED = 4;
	const STATUS_CONFIRMED_AS_DELIVERED = 5;
	const STATUS_CONFIRMED_AS_LOST = 6;

	const WITHOUT_TAXES = false;

	public $ID;
	public $orderID;
	//public $shippingServiceID", "ShippingService", "ID", "ShippingService;
	//public $shippingAddressID", "shippingAddress", "ID", 'UserAddress;

	public $trackingCode;
	public $dateShipped;
	public $amount;
	public $taxAmount;
	public $shippingAmount;
	public $status;
	public $shippingServiceData;

	public function initialize()
	{
		$this->belongsTo('orderID', 'order\CustomerOrder', 'ID', array('foreignKey' => true, 'alias' => 'CustomerOrder'));
	}

	/*####################  Static method implementations ####################*/

	public static function getNewInstance(CustomerOrder $order)
	{
		$instance = new self();
		$instance->order = $order;
		return $instance;
	}

	/*####################  Value retrieval and manipulation ####################*/

	public function loadItems()
	{
		if (empty($this->items) && $this->getID())
		{
			$filter = new ARSelectFilter();
			$filter->setCondition('OrderedItem.shipmentID = :OrderedItem.shipmentID:', array('OrderedItem.shipmentID' => $this->getID()));

			foreach(OrderedItem::getRecordSet('OrderedItem', $filter, array('Product', 'Category', 'DefaultImage' => 'ProductImage')) as $item)
			{
				$this->items[] = $item;
			}
		}
	}

	public function addItem(OrderedItem $item)
	{
		$this->loadItems();

		foreach ($this->items as $key => $shipmentItem)
		{
			if ($shipmentItem === $item)
			{
				return;
			}
		}

		$this->items[] = $item;
		$item->shipment = $this;

		$this->markAsModified();
	}

	public function removeItem(OrderedItem $item)
	{
		foreach($this->items as $key => $shipmentItem)
		{
			if($shipmentItem === $item)
			{
				unset($this->items[$key]);
				$item->shipment = null;

				$this->markAsModified();
				break;
			}
		}
	}

	private function markAsModified()
	{
		$this->isModified = true;
	}

	public function isModified()
	{
		if ($this->isModified)
		{
			return true;
		}

		return parent::isModified();
	}

	public function getShippingAddress()
	{
		foreach (array($this, $this->order) as $parent)
		{
			if (!$parent)
			{
				continue;
			}

			if (!$parent->isLoaded())
			{
				$parent->load(array('ShippingAddress'));
			}

			if ($address = $parent->shippingAddress)
			{
				return $address;
			}
		}
	}

	public function getTaxZone()
	{
		$address = $this->getShippingAddress();
		$addressID = $address ? $address->getID() : 0;

		if (!isset($this->taxZones[$addressID]))
		{
			if ($address)
			{
				$this->taxZones[$addressID] = DeliveryZone::getZoneByAddress($address, DeliveryZone::TAX_RATES);
			}
			else
			{
				$this->taxZones[$addressID] = DeliveryZone::getDefaultZoneInstance();
			}
			$this->event('getTaxZone'); // ? getDeliveryZone
		}

		return $this->taxZones[$addressID];
	}

	public function getDeliveryZone()
	{
		$address = $this->getShippingAddress();
		$addressID = $address ? $address->getID() : 0;

		if (!isset($this->deliveryZones[$addressID]))
		{
			if ($address)
			{
				$this->deliveryZones[$addressID] = DeliveryZone::getZoneByAddress($address, DeliveryZone::SHIPPING_RATES);
			}
			else
			{
				$this->deliveryZones[$addressID] = DeliveryZone::getDefaultZoneInstance();
			}

			$this->event('getDeliveryZone');
		}

		return $this->deliveryZones[$addressID];
	}

	public function setDeliveryZone(DeliveryZone $zone)
	{
		$address = $this->getShippingAddress();
		$addressID = $address ? $address->getID() : 0;

		$this->deliveryZones[$addressID] = $zone;
	}

	public function setTaxZone(DeliveryZone $zone)
	{
		$address = $this->getShippingAddress();
		$addressID = $address ? $address->getID() : 0;

		$this->taxZones[$addressID] = $zone;
	}

	public function getChargeableWeight(DeliveryZone $zone = null)
	{
		$weight = 0;

		if (is_null($zone))
		{
			$zone = $this->getDeliveryZone();
		}

		foreach ($this->items as $item)
		{
			if (!$item->getProduct()->isFreeShipping || !$zone->isFreeShipping)
			{
				$weight += ($item->getProduct()->getShippingWeight() * $item->count);
			}
		}

		return $weight;
	}

	public function getChargeableItemCount(DeliveryZone $zone)
	{
		$count = 0;

		foreach ($this->items as $item)
		{
			if (!$item->getProduct()->isFreeShipping || !$zone->isFreeShipping)
			{
				$count += $item->count;
			}
		}

		return $count;
	}

	public function getShippingRates()
	{
		return $this->getDeliveryZone()->getShippingRates($this);
	}

	public function setAvailableRates(ShippingRateSet $rates)
	{
		$this->availableShippingRates = $rates;
	}

	public function getAvailableRates()
	{
		if (!$this->availableShippingRates)
		{
			$this->setAvailableRates($this->getShippingRates());
		}

		return $this->availableShippingRates;
	}

	public function setRateId($serviceId)
	{
		if (!$this->getAvailableRates())
		{
			$this->setAvailableRates($this->getShippingRates());
		}

		$this->selectedRateId = $serviceId;

		if ($this->getCustomerorderBy()->isMultiAddress)
		{
			$this->shippingServiceData = serialize($this->getSelectedRate());
		}
	}

	public function getRateId()
	{
		if ($this->getCustomerorderBy()->isMultiAddress && !$this->selectedRateId)
		{
			$this->selectedRateId = unserialize($this->shippingServiceData)->getServiceId();
		}

		return $this->selectedRateId;
	}

	public function isShippable()
	{
		$this->removeDeletedItems();

		foreach ($this->items as $item)
		{
			if (!$item->isLoaded())
			{
				continue;
			}

			if ($item->getProduct()->isDownloadable())
			{
				return false;
			}
		}

		return true;
	}

	public function getSubTotal($applyTaxes = true)
	{
		$subTotal = 0;
		$taxes = 0;

		foreach ($this->items as $item)
		{
			if (!$item->isDeleted())
			{
				$subTotal += $item->getSubTotal(false);
				$taxes += $item->getTaxAmount();
			}
		}

		if ($applyTaxes)
		{
			$subTotal += $taxes;
		}

		return $subTotal;
		//return $this->getCurrency()->round($subTotal);
	}

	public function getSubTotalBeforeTax()
	{
		$this->recalculateAmounts(false);
		return $this->amount;
	}

	public function getShippingTotalBeforeTax()
	{
		$this->recalculateAmounts(false);
		return $this->shippingAmount;
	}

	public function getShippingTotalWithTax()
	{
		$this->recalculateAmounts(false);
		$total = $this->shippingAmount;

		if (is_null($total))
		{
			return null;
		}

		foreach ($this->getTaxes() as $tax)
		{
			if ($tax->isShippingTax())
			{
				$total += $tax->getAmount();
			}
		}

		return $this->getCurrency()->round($total);
	}

	public function getTotalWithoutTax()
	{
		$this->recalculateAmounts(false);
		return $this->amount + $this->shippingAmount;
	}

	public function getTotal($recalculate = false)
	{
		if ($recalculate)
		{
			$this->recalculateAmounts();
		}

		return $this->amount + $this->shippingAmount + $this->taxAmount;
	}

	public function isProcessing()
	{
		return $this->status == self::STATUS_PROCESSING;
	}

	public function isAwaitingShipment()
	{
		return $this->status == self::STATUS_AWAITING;
	}

	public function isShipped()
	{
		return $this->status == self::STATUS_SHIPPED;
	}

	public function isReturned()
	{
		return $this->status == self::STATUS_RETURNED;
	}

	public function isDelivered()
	{
		return $this->status == self::STATUS_CONFIRMED_AS_DELIVERED;
	}

	public function isLost()
	{
		return $this->status == self::STATUS_CONFIRMED_AS_LOST;
	}

	/**
	 *	Apply a fixed amount discount to shipment total
	 *	This is a little tricky to calculate as the fixed discount must be split over all shipments
	 *	and must be applied to order total after taxes
	 */
	public function applyFixedDiscount($orderTotal, $discountAmount)
	{
		// calculate discount amount that applies to this shipment
		$shipmentDiscount = ($this->getTotal() / $orderTotal) * $discountAmount;
		$discountMultiplier = 1 - ($this->getTotal() ? ($shipmentDiscount / $this->getTotal()) : 0);

		foreach ($this->getTaxes() as $tax)
		{
			$tax->amount = $tax->amount * $discountMultiplier;
		}

		$origShippingAmount = $this->shippingAmount;
		foreach (array('amount', 'shippingAmount', 'taxAmount') as $amount)
		{
			$this->$amount = $this->$amount * $discountMultiplier;
		}

		$this->amount = $this->amount + ($this->shippingAmount - $origShippingAmount);
		$this->shippingAmount = $origShippingAmount;
	}

	public function applyTaxesToShippingAmount($amount)
	{
		$taxAmount = 0;

		$originalAmount = $amount;

		foreach ($this->getTaxes() as $tax)
		{
			if ($tax->type == ShipmentTax::TYPE_SHIPPING)
			{
				$amount = $originalAmount + $taxAmount;

				if ($tax->taxRate)
				{
					$taxAmount += $tax->taxRate->applyTax($amount) - $amount;
				}
				else
				{
					$taxAmount += $tax->amount;
				}

			}
		}

		return $originalAmount + $taxAmount;
	}

	public function reduceTaxesFromShippingAmount($amount)
	{
		foreach ($this->getTaxes() as $tax)
		{
			if ($tax->type == ShipmentTax::TYPE_SHIPPING)
			{
				$amount  = $amount / (1 + ($tax->taxRate->rate / 100));
			}
		}

		return $amount;
	}

	public function recalculateAmounts($calculateTax = true)
	{
		$this->loadItems();

		$currency = $this->getCustomerorderBy()->getCurrency();

		$itemAmount = $this->getSubTotal(self::WITHOUT_TAXES);
		$this->amount = $itemAmount;

		// total taxes
		if ($calculateTax)
		{
			$deleted = false;

			if ($this->getCustomerorderBy()->isFinalized)
			{
				if ($this->getID())
				{
					$this->deleteRelatedRecordSet('ShipmentTax');
				}

				$this->taxes = null;
				$deleted = true;
			}

			$roundedTaxAmount = $taxes = array(ShipmentTax::TYPE_SUBTOTAL => 0, ShipmentTax::TYPE_SHIPPING => 0);
			foreach ($this->getTaxes() as $tax)
			{
				$tax->recalculateAmount(false);
				$amount = $tax->getAmount();
				$taxes[$tax->type] += $amount;
				$roundedTaxAmount[$tax->type] += $currency->round($amount);
				if ($deleted)
				{
					$tax->save();
				}
			}

			// correct rounding sum errors (offsets by 0.01, etc)
			foreach ($taxes as $type => $taxAmount)
			{
				foreach ($this->getTaxes() as $tax)
				{
					if ($tax->type != $type)
					{
						continue;
					}

					$diff = $roundedTaxAmount[$type] - $currency->round($taxes[$type]);
					if (!$diff || (abs($diff) < 0.01))
					{
						break;
					}

					$amount = $tax->getAmount();
					if ($diff > 0)
					{
						$tax->amount = floor($amount * 100) / 100;
						$roundedTaxAmount[$type] -= 0.01;
					}
					else
					{
						$tax->amount = ceil($amount * 100) / 100;
						$roundedTaxAmount[$type] += 0.01;
					}
				}
			}

			// round individual tax amounts
			$totalTaxes = 0;
			foreach ($this->getTaxes() as $tax)
			{
				$tax->amount = $currency->round($tax->getAmount());
				$totalTaxes += $tax->getAmount();
			}

			$this->taxAmount = $totalTaxes;
		}

		// shipping rate
		if (($rate = $this->getSelectedRate()) && $this->isShippable())
		{
			$amount = $rate->getAmountByCurrency($currency);

			if ($this->getDeliveryZone()->isDefault())
			{
				$amount = $this->reduceTaxesFromShippingAmount($amount);
			}

			$this->shippingAmount = $calculateTax ? $currency->round($amount) : $amount;
		}

		$this->amount = $currency->round($itemAmount);
	}

	public function addFixedTax(ShipmentTax $tax)
	{
		$tax->shipment = $this;

		if ($this->taxes)
		{
			$this->taxes->add($tax);
		}
		else
		{
			$this->fixedTaxes[] = $tax;
		}
	}

	private function removeDeletedItems()
	{
		foreach ($this->items as $key => $item)
		{
			// Don't try to delete new records
			if(!$item->getID()) continue;

			if ($item->isDeleted())
			{
				unset($this->items[$key]);
			}

			if (!$item->isLoaded())
			{
				try
				{
					$item->load(true);
				}
				catch (ARNotFoundException $e)
				{
					unset($this->items[$key]);
				}
			}
		}
	}

	/*####################  Saving ####################*/

	public function beforeSave()
	{
		$this->removeDeletedItems();

		// make sure the shipment doesn't consist of downloadable files only
		if (!$this->isShippable() && !$this->getCustomerorderBy()->isFinalized)
		{
			//return false;
		}

		// reset amounts...
//		$this->amount = 0);
//		$this->shippingAmount = 0);
//		$this->taxAmount = 0);

		// ... and recalculated them
		$this->recalculateAmounts();

		// set shipping data
		$rate = $this->getSelectedRate();

		if ($rate)
		{
			$serviceId = $rate->getServiceID();
			if (is_numeric($serviceId))
			{
				$this->shippingService = ShippingService::getInstanceByID($serviceId);
			}
			else
			{
				$this->shippingService = null;
				$this->shippingServiceData = serialize($rate);
			}
		}

		// Update order status if to reflect it's shipments statuses
		if (!$downloadable && $this->isShippable() && $this->getCustomerorderBy()->isFinalized)
		{
			$this->getCustomerorderBy()->updateStatusFromShipments(!$this->getID());
		}
	}

	public function afterSave()
	{
		// save ordered items
		foreach ($this->items as $item)
		{
			if(!$item->isDeleted())
			{
				$item->shipment = $this;
				$item->customerOrder = $this->order;
				$item->save();
			}
		}

		// save taxes
		foreach ($this->getTaxes() as $tax)
		{
			if ($tax->amount)
			{
				$tax->save();
			}
			else
			{
				$tax->delete();
			}
		}
	}

	public function delete()
	{
		$order = $this->order;

		$order->removeShipment($this);

		parent::delete();

		$order->save();
	}

	public function beforeCreate()
	{
		// the shipment objects are often restored from serialized state, so we must mark all fields as modified
		foreach ($this->data as $field)
		{
			if (!$field->isNull())
			{
				$field->setAsModified();
			}
		}

		// Save updated order status
		if ($this->getCustomerorderBy()->isFinalized)
		{
			$this->getCustomerorderBy()->save();
		}

		if(!$this->status)
		{
			$this->status = self::STATUS_NEW;
		}


	}

	protected function afterUpdate()
	{
		$this->getCustomerorderBy()->save();
	}

	/*####################  Data array transformation ####################*/

	public function toArray()
	{
		$array = parent::toArray();
		$currency = $this->getCurrency();
		$id = $currency->getID();

		// ordered items
		$items = array();
		foreach ($this->items as $item)
		{
			$items[] = $item->toArray();
		}
		$array['items'] = $items;

		// subtotal
		$currencies = self::getApplication()->getCurrencySet();
		$array['subTotal'][$id] = $this->getSubTotal();

		// total amount
		$array['totalAmount'] = $this->getTotal();
		$array['formatted_totalAmount'] = $this->getCustomerorderBy()->currency->getFormattedPrice($array['totalAmount']);
		$array['formatted_amount'] = $this->getCustomerorderBy()->currency->getFormattedPrice($array['amount']);

		// formatted subtotal
		$array['formattedSubTotal'] = $array['formattedSubTotalBeforeTax'] = array();
		$array['formattedSubTotal'][$id] = $currency->getFormattedPrice($array['subTotal'][$id]);
		$array['formattedSubTotalBeforeTax'][$id] = $currency->getFormattedPrice($array['subTotal'][$id] - $this->getTaxAmount());

		// selected shipping rate
		if ($selected = $this->getSelectedRate())
		{
			$array['selectedRate'] = $selected->toArray($this->applyTaxesToShippingAmount($selected->getCostAmount()));

			if (!$array['selectedRate'])
			{
				unset($array['selectedRate']);
			}
			else
			{
				$array['ShippingService'] = $array['selectedRate']['ShippingService'];
			}
		}

		// shipping rate for a saved shipment
		if (!isset($array['selectedRate']) && isset($array['shippingAmount']))
		{
			$array['shippingAmountWithoutTax'] = $array['shippingAmount'];
			$array['shippingAmount'] = $this->applyTaxesToShippingAmount($array['shippingAmount']);
			$orderCurrency = $this->getCustomerorderBy()->currency;
			$array['selectedRate']['formattedPrice'] = array();
			foreach ($currencies as $id => $currency)
			{
				$rate = $currency->convertAmount($orderCurrency, $array['shippingAmount']);
				$array['selectedRate']['formattedPrice'][$id] = Currency::getInstanceById($id)->getFormattedPrice($rate);
				$array['selectedRate']['formattedPriceWithoutTax'][$id] = Currency::getInstanceById($id)->getFormattedPrice($array['shippingAmountWithoutTax']);
			}
		}

		// taxes
		$taxes = array();
		foreach ($this->getTaxes() as $tax)
		{
			if ($tax->taxRate)
			{
				$taxes[$tax->taxRate->tax->getID()][] = $tax;
			}
		}

		foreach ($taxes as $taxType)
		{
			$amount = 0;
			foreach ($taxType as $tax)
			{
				$amount += $tax->amount;
			}

			if ($amount > 0)
			{
				$array['taxes'][] = $tax->toArray($amount);
			}
		}

		// consists of downloadable files only?
		$array['isShippable'] = $this->isShippable();

		// Statuses
		$array['isReturned'] = (int)$this->isReturned();;
		$array['isShipped'] = (int)$this->isShipped();
		$array['isAwaitingShipment'] = (int)$this->isAwaitingShipment();
		$array['isProcessing'] = (int)$this->isProcessing();
		$array['isDelivered'] = (int)$this->isDelivered();
		$array['isLost'] = (int)$this->isLost();

		$array['AmountCurrency'] =& $array['Order']['Currency'];

		return $array;
	}

	/*####################  Get related objects ####################*/

	public function getCurrency()
	{

		$this->getCustomerorderBy()->load();
		return $this->getCustomerorderBy()->getCurrency();
	}

	public function getSelectedRate()
	{
		if (($serializedRate = $this->shippingServiceData) && ($rate = unserialize($serializedRate)))
		{
			$rate->setApplication($this->getApplication());

			if (($this->getRateId() == $rate->getServiceId()) || ($this->getCustomerorderBy()->isFinalized))
			{
				return $rate;
			}
		}

		if (!$this->availableShippingRates)
		{
			return null;
		}

		return $this->availableShippingRates->getByServiceId($this->selectedRateId);
	}

	public function getShippingTaxZone()
	{
		$shippingTaxZoneId = $this->getConfig()->get('DELIVERY_TAX');
		return !is_numeric($shippingTaxZoneId) ? $this->getTaxZone() : DeliveryZone::getInstanceById($shippingTaxZoneId, DeliveryZone::LOAD_DATA);
	}

	public function getShippingTaxClass()
	{
		$shippingTaxClassId = $this->getConfig()->get('DELIVERY_TAX_CLASS');
		return !is_numeric($shippingTaxClassId) ? null : TaxClass::getInstanceById($shippingTaxClassId, TaxClass::LOAD_DATA);
	}

	public function getTaxes()
	{
		if (!$this->taxes)
		{


			if ($this->isLoaded() && $this->getCustomerorderBy()->isFinalized)
			{
				$this->taxes = $this->getRelatedRecordSet('ShipmentTax', new ARSelectFilter(), array('Tax', 'TaxRate'));
				foreach ($this->fixedTaxes as $tax)
				{
					$this->taxes->add($tax);
				}
			}

			if (!$this->taxes || !$this->taxes->count())
			{
				$this->taxes = new ARSet();
				$taxes = array();

				// subtotal tax rates
				$zone = $this->getTaxZone();
				foreach ($zone->getTaxRates(DeliveryZone::ENABLED_TAXES) as $rate)
				{
					$taxes[$rate->getPosition()][ShipmentTax::TYPE_SUBTOTAL] = $rate;
				}

				// shipping amount tax rates
				$shippingTaxZone = $this->getShippingTaxZone();
				$shippingTaxClass = $this->getShippingTaxClass();
				foreach ($shippingTaxZone->getTaxRates(DeliveryZone::ENABLED_TAXES) as $rate)
				{
					if ($rate->taxClass === $shippingTaxClass)
					{
						$taxes[$rate->getPosition()][ShipmentTax::TYPE_SHIPPING] = $rate;
					}
				}

				foreach ($taxes as $taxRates)
				{
					foreach ($taxRates as $type => $rate)
					{
						$shipmentTax = ShipmentTax::getNewInstance($rate, $this, $type);
						$this->taxes->add($shipmentTax);
					}
				}
			}
		}

		return $this->taxes;
	}

	public function getAppliedTaxes()
	{
		return $this->taxes;
	}

	public function getTaxAmount()
	{
		$amount = 0;

		foreach ($this->getTaxes() as $tax)
		{
			$amount += $tax->getAmount();
		}

		return $amount;
	}

	public function getShippingService()
	{
		if($this->shippingService)
		{
			return $this->shippingService;
		}
		else if($this->shippingServiceData)
		{
			$rate = unserialize($this->shippingServiceData);
			return ShippingService::getInstanceByID($rate->getServiceID());
		}
		else
		{
			return null;
		}
	}

	public function getItems()
	{
		$this->loadItems();
		return $this->items;
	}

	public function serialize()
	{
		$this->itemIds = array();
		foreach ($this->items as $item)
		{
			$this->itemIds[] = $item->getID();
		}

		return parent::serialize(array('orderID'), array('itemIds', 'availableShippingRates', 'selectedRateId'));
	}

	public function unserialize($serialized)
	{
		parent::unserialize($serialized);

		if ($this->availableShippingRates)
		{
			foreach($this->availableShippingRates as $rate)
			{
				$rate->setApplication($this->getApplication());
			}
		}

		if ($this->itemIds)
		{
			$this->items = array();
			foreach ($this->itemIds as $id)
			{
				if ($id)
				{
					try
					{
						$this->items[] = OrderedItem::getInstanceByID($id, ActiveRecordModel::LOAD_DATA);
					}
					catch (ARNotFoundException $e)
					{
					}
				}
			}

			$this->itemIds = array();
		}
	}

	public function __clone()
	{
		parent::__clone();

		$original = $this->originalRecord;

		$this->items = array();
		foreach ($original->getItems() as $item)
		{
			$this->addItem(clone $item);
		}
	}

	public function __destruct()
	{
		$this->taxes = null;
		$this->fixedTaxes = null;
		$this->items = null;

		parent::__destruct();
	}
}

?>