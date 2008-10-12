<?php

ClassLoader::import("application.model.product.Product");
ClassLoader::import("application.model.order.OrderedItem");
ClassLoader::import("application.model.order.ShipmentTax");

/**
 * Represents a collection of ordered items that are shipped in the same package
 *
 * @package application.model.order
 * @author Integry Systems <http://integry.com>
 */
class Shipment extends ActiveRecordModel
{
	public $items = array();

	/**
	 *  Used only for serialization
	 */
	protected $itemIds = array();

	protected $availableShippingRates = array();

	protected $selectedRateId;

	protected $fixedTaxes = array();

	const STATUS_NEW = 0;
	const STATUS_PROCESSING = 1;
	const STATUS_AWAITING = 2;
	const STATUS_SHIPPED = 3;
	const STATUS_RETURNED = 4;
	const STATUS_CONFIRMED_AS_DELIVERED = 5;
	const STATUS_CONFIRMED_AS_LOST = 6;

	const WITHOUT_TAXES = false;

	/**
	 * Define database schema used by this active record instance
	 *
	 * @param string $className Schema name
	 */
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("orderID", "CustomerOrder", "ID", "CustomerOrder", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("amountCurrencyID", "Currency", "ID", "Currency", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("shippingServiceID", "ShippingService", "ID", "ShippingService", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("shippingAddressID", "shippingAddress", "ID", 'UserAddress', ARInteger::instance()));

		$schema->registerField(new ARField("trackingCode", ARVarchar::instance(100)));
		$schema->registerField(new ARField("dateShipped", ARDateTime::instance()));
		$schema->registerField(new ARField("amount", ARFloat::instance()));
		$schema->registerField(new ARField("taxAmount", ARFloat::instance()));
		$schema->registerField(new ARField("shippingAmount", ARFloat::instance()));
		$schema->registerField(new ARField("status", ARInteger::instance(2)));
		$schema->registerField(new ARField("shippingServiceData", ARText::instance(50)));
		//$schema->registerAutoReference('shippingAddressID');
	}

	/*####################  Static method implementations ####################*/

	public static function getNewInstance(CustomerOrder $order)
	{
		$instance = parent::getNewInstance(__class__);
		$instance->order->set($order);
		return $instance;
	}

	/*####################  Value retrieval and manipulation ####################*/

	public function loadItems()
	{
		if (empty($this->items) && $this->isExistingRecord())
		{
			$filter = new ARSelectFilter();
			$filter->setCondition(new EqualsCond(new ARFieldHandle('OrderedItem', 'shipmentID'), $this->getID()));

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
		$item->shipment->set($this);

		$this->markAsModified();
	}

	public function removeItem(OrderedItem $item)
	{
		foreach($this->items as $key => $shipmentItem)
		{
			if($shipmentItem === $item)
			{
				unset($this->items[$key]);
				$item->shipment->setNull();

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
		foreach (array($this->shippingAddress->get(), $this->order->get()->shippingAddress->get()) as $address)
		{
			if ($address)
			{
				return $address;
			}
		}
	}

	public function getDeliveryZone()
	{
		if (!$this->deliveryZone)
		{
			if ($address = $this->getShippingAddress())
			{
				$this->deliveryZone = DeliveryZone::getZoneByAddress($address);
			}
			else
			{
				$this->deliveryZone = DeliveryZone::getDefaultZoneInstance();
			}
		}

		return $this->deliveryZone;
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
			if (!$item->product->get()->isFreeShipping->get() || !$zone->isFreeShipping->get())
			{
				$weight += ($item->product->get()->getShippingWeight() * $item->count->get());
			}
		}

		return $weight;
	}

	public function getChargeableItemCount(DeliveryZone $zone)
	{
		$count = 0;

		foreach ($this->items as $item)
		{
			if (!$item->product->get()->isFreeShipping->get() || !$zone->isFreeShipping->get())
			{
				$count += $item->count->get();
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

		if ($this->order->get()->isMultiAddress->get())
		{
			$this->shippingServiceData->set(serialize($this->getSelectedRate()));
		}
	}

	public function getRateId()
	{
		if ($this->order->get()->isMultiAddress->get() && !$this->selectedRateId)
		{
			$this->selectedRateId = unserialize($this->shippingServiceData->get())->getServiceId();
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

			if ($item->product->get()->isDownloadable())
			{
				return false;
			}
		}

		return true;
	}

	public function getSubTotal(Currency $currency, $applyTaxes = true)
	{
		$subTotal = 0;
		foreach ($this->items as $item)
		{
			if (!$item->isDeleted())
			{
				$subTotal += $item->getSubTotal($currency);
			}
		}

		if ($applyTaxes)
		{
			$subTotal = $this->applyTaxesToAmount($subTotal);
		}

		return $subTotal;
	}

	public function getSubTotalBeforeTax()
	{
		$this->recalculateAmounts(false);
		return $this->amount->get();
	}

	public function getShippingTotalBeforeTax()
	{
		$this->recalculateAmounts(false);
		return $this->shippingAmount->get();
	}

	public function getTotalWithoutTax()
	{
		$this->recalculateAmounts(false);
		return $this->amount->get() + $this->shippingAmount->get();
	}

	public function getTotal($recalculate = true)
	{
		if ($recalculate)
		{
			$this->recalculateAmounts();
		}

		return $this->amount->get() + $this->shippingAmount->get() + $this->taxAmount->get();
	}

	public function isProcessing()
	{
		return $this->status->get() == self::STATUS_PROCESSING;
	}

	public function isAwaitingShipment()
	{
		return $this->status->get() == self::STATUS_AWAITING;
	}

	public function isShipped()
	{
		return $this->status->get() == self::STATUS_SHIPPED;
	}

	public function isReturned()
	{
		return $this->status->get() == self::STATUS_RETURNED;
	}

	public function isDelivered()
	{
		return $this->status->get() == self::STATUS_CONFIRMED_AS_DELIVERED;
	}

	public function isLost()
	{
		return $this->status->get() == self::STATUS_CONFIRMED_AS_LOST;
	}

	/**
	 *	Apply a fixed amount discount to shipment total
	 *	This is a little tricky to calculate as the fixed discount must be split over all shipments
	 *	and must be applied to order total after taxes
	 */
	public function applyFixedDiscount($orderTotal, $discountAmount)
	{
		// calculate discount amount that applies to this shipment
		$shipmentTotal = $this->getTotal();
		$shipmentDiscount = ($this->getTotal() / $orderTotal) * $discountAmount;
		$discountMultiplier = 1 - ($shipmentDiscount / $shipmentTotal);

		foreach ($this->getTaxes() as $tax)
		{
			$tax->amount->set($tax->amount->get() * $discountMultiplier);
		}

		foreach (array('amount', 'shippingAmount', 'taxAmount') as $amount)
		{
			$this->$amount->set($this->$amount->get() * $discountMultiplier);
		}
	}

	public function applyTaxesToAmount($amount)
	{
		$taxAmount = 0;

		foreach ($this->getTaxes() as $tax)
		{
			if ($tax->isItemTax())
			{
				if ($tax->taxRate->get())
				{
					$taxAmount += $tax->taxRate->get()->applyTax($amount) - $amount;
				}
				else
				{
					$taxAmount += $tax->amount->get();
				}
			}
		}

		return $amount + $taxAmount;
	}

	public function reduceTaxesFromAmount($amount)
	{
		foreach ($this->getTaxes() as $tax)
		{
			$amount  = $amount / (1 + ($tax->taxRate->get()->rate->get() / 100));
		}

		return $amount;
	}

	public function recalculateAmounts($calculateTax = true)
	{
		$this->loadItems();

		$currency = $this->order->get()->currency->get();
		if (!$currency)
		{
			$currency = $this->getApplication()->getDefaultCurrency();
		}

		$this->amountCurrency->set($currency);
		$this->amount->set($this->getSubTotal($currency, self::WITHOUT_TAXES));

		// total taxes
		if ($calculateTax)
		{
			if ($this->isLoaded())
			{
				$this->deleteRelatedRecordSet('ShipmentTax');
				$this->taxes = null;
			}

			$taxes = 0;
			foreach ($this->getTaxes() as $tax)
			{
				$tax->recalculateAmount(false);
				$taxes += $tax->getAmountByCurrency($currency);
			}

			$this->taxAmount->set($taxes);
		}

		// shipping rate
		if (($rate = $this->getSelectedRate()) && $this->isShippable())
		{
			$amount = $rate->getAmountByCurrency($currency);
			if ($this->getDeliveryZone()->isDefault())
			{
				$amount = $this->reduceTaxesFromAmount($amount);
			}

			$this->shippingAmount->set($amount);
		}
	}

	public function addFixedTax(ShipmentTax $tax)
	{
		$tax->shipment->set($this);

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
			if(!$item->isExistingRecord()) continue;

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

	public function save($downloadable = false)
	{
		$this->removeDeletedItems();

		// make sure the shipment doesn't consist of downloadable files only
		if (!$this->isShippable() && !$this->order->get()->isFinalized->get())
		{
			//return false;
		}

		// reset amounts...
//		$this->amount->set(0);
//		$this->shippingAmount->set(0);
//		$this->taxAmount->set(0);

		// ... and recalculated them
		$this->recalculateAmounts();

		// set shipping data
		$rate = $this->getSelectedRate();

		if ($rate)
		{
			$serviceId = $rate->getServiceID();
			if (is_numeric($serviceId))
			{
				$this->shippingService->set(ShippingService::getInstanceByID($serviceId));
			}
			else
			{
				$this->shippingService->set(null);
				$this->shippingServiceData->set(serialize($rate));
			}
		}

		// Update order status if to reflect it's shipments statuses
		if (!$downloadable && $this->isShippable() && $this->order->get()->isFinalized->get())
		{
			$this->order->get()->updateStatusFromShipments(!$this->isExistingRecord());
		}

		parent::save();

		// save ordered items
		foreach ($this->items as $item)
		{
			if(!$item->isDeleted())
			{
				$item->shipment->set($this);
				$item->customerOrder->set($this->order->get());
				$item->save();
			}
		}

		// save taxes
		foreach ($this->getTaxes() as $tax)
		{
			if ($tax->amount->get())
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
		$order = $this->order->get();

		$order->removeShipment($this);

		parent::delete();

		$order->save();
	}

	protected function insert()
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
		if ($this->order->get()->isFinalized->get())
		{
			$this->order->get()->save();
		}

		if(!$this->status->get())
		{
			$this->status->set(self::STATUS_NEW);
		}

		return parent::insert();
	}

	protected function update()
	{
		parent::update();

		$this->order->get()->save();
	}

	/*####################  Data array transformation ####################*/

	public function toArray()
	{
		$array = parent::toArray();

		// ordered items
		$items = array();
		foreach ($this->items as $item)
		{
			$items[] = $item->toArray();
		}
		$array['items'] = $items;

		// subtotal
		$currencies = self::getApplication()->getCurrencySet();
		$subTotal = array();
		foreach ($currencies as $id => $currency)
		{
			$subTotal[$id] = $this->getSubTotal($currency);
		}
		$array['subTotal'] = $subTotal;

		// total amount
		$array['totalAmount'] = $this->getTotal();
		$array['formatted_totalAmount'] = $this->order->get()->currency->get()->getFormattedPrice($array['totalAmount']);
		$array['formatted_amount'] = $this->order->get()->currency->get()->getFormattedPrice($array['amount']);

		// formatted subtotal
		$array['formattedSubTotal'] = $array['formattedSubTotalBeforeTax'] = array();
		foreach ($subTotal as $id => $price)
		{
			$currency = Currency::getInstanceById($id);
			$array['formattedSubTotal'][$id] = $currency->getFormattedPrice($price);
			$array['formattedSubTotalBeforeTax'][$id] = $currency->getFormattedPrice($price - $this->getTaxAmount($currency));
		}

		// selected shipping rate
		if ($selected = $this->getSelectedRate())
		{
			$array['selectedRate'] = $selected->toArray();
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
			$currency = $this->order->get()->currency->get();
			$array['selectedRate']['formattedPrice'] = array();
			foreach ($currencies as $id => $currency)
			{
				$rate = $currency->convertAmount($currency, $array['shippingAmount']);
				$array['selectedRate']['formattedPrice'][$id] = Currency::getInstanceById($id)->getFormattedPrice($rate);
			}
		}

		// taxes
		$taxes = array();
		foreach ($this->getTaxes() as $tax)
		{
			$taxes[$tax->taxRate->get()->getID()][] = $tax;
		}

		foreach ($taxes as $taxType)
		{
			$amount = 0;
			foreach ($taxType as $tax)
			{
				$amount += $tax->amount->get();
			}

			$array['taxes'][] = $tax->toArray($amount);
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

		return $array;
	}

	/*####################  Get related objects ####################*/

	public function getSelectedRate()
	{
		if (($serializedRate = $this->shippingServiceData->get()) && ($rate = unserialize($serializedRate)))
		{
			$rate->setApplication($this->getApplication());

			if($this->getRateId() == $rate->getServiceId())
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

	public function getTaxes()
	{
		// no taxes are calculated for downloadable products
		if (!$this->isShippable())
		{
			//return new ARSet();
		}

		if (!$this->taxes)
		{
			$this->load();

			if ($this->isLoaded() && $this->order->get()->isFinalized->get())
			{
				$this->taxes = $this->getRelatedRecordSet('ShipmentTax', new ARSelectFilter(), array('Tax', 'TaxRate'));
				foreach ($this->fixedTaxes as $tax)
				{
					$this->taxes->add($tax);
				}
			}

			if (!$this->taxes || !$this->taxes->size())
			{
				$this->taxes = new ARSet();
				$taxes = array();

				// subtotal tax rates
				$zone = $this->getDeliveryZone();
				foreach ($zone->getTaxRates(DeliveryZone::ENABLED_TAXES) as $rate)
				{
					$taxes[$rate->tax->get()->position->get()][ShipmentTax::TYPE_SUBTOTAL] = $rate;
				}

				// shipping amount tax rates
				$shippingTaxZoneId = self::getApplication()->getConfig()->get('DELIVERY_TAX');
				$shippingTaxZone = !is_numeric($shippingTaxZoneId) ? $zone : DeliveryZone::getInstanceById($shippingTaxZoneId, DeliveryZone::LOAD_DATA);

				foreach ($shippingTaxZone->getTaxRates(DeliveryZone::ENABLED_TAXES) as $rate)
				{
					$taxes[$rate->tax->get()->position->get()][ShipmentTax::TYPE_SHIPPING] = $rate;
				}

				foreach ($taxes as $taxRates)
				{
					foreach ($taxRates as $type => $rate)
					{
						$this->taxes->add(ShipmentTax::getNewInstance($rate, $this, $type));
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

	public function getTaxAmount(Currency $currency)
	{
		$amount = 0;

		foreach ($this->getTaxes() as $tax)
		{
			$amount += $tax->getAmountByCurrency($currency);
		}

		return $amount;
	}

	public function getShippingService()
	{
		if($this->shippingService->get())
		{
			return $this->shippingService->get();
		}
		else if($this->shippingServiceData->get())
		{
			$rate = unserialize($this->shippingServiceData->get());
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
				try
				{
					$this->items[] = ActiveRecordModel::getInstanceById('OrderedItem', $id, ActiveRecordModel::LOAD_DATA);
				}
				catch (ARNotFoundException $e)
				{
				}
			}

			$this->itemIds = array();
		}
	}

	public function __clone()
	{
		parent::__clone();

		$original = $this->originalRecord;

		foreach ($original->getItems() as $item)
		{
			$this->addItem(clone $item);
		}
	}
}

?>