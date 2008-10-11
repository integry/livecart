<?php

require_once dirname(__FILE__) . '/OrderTestCommon.php';

/**
 *	Test ShipmentTax model
 *
 *  @author Integry Systems
 *  @package test.model.order
 */
class ShipmentTaxTest extends OrderTestCommon
{
	public function testTaxWithDifferentZone()
	{
		$tax = Tax::getNewInstance('VAT');
		$tax->save();

		// shipment delivery zone
		$zone = DeliveryZone::getNewInstance();
		$zone->name->set('USA');
		$zone->save();

		$country = DeliveryZoneCountry::getNewInstance($zone, 'US');
		$country->save();

		$taxRate = TaxRate::getNewInstance($zone, $tax, 20);
		$taxRate->save();

		$service = ShippingService::getNewInstance($zone, 'def', ShippingService::SUBTOTAL_BASED);
		$service->save();

		// shipping amount zone
		$zone = DeliveryZone::getNewInstance();
		$zone->name->set('Random');
		$zone->save();

		$taxRate = TaxRate::getNewInstance($zone, $tax, 50);
		$taxRate->save();

		$this->config->set('DELIVERY_TAX', $zone->getID());

		$shippingRate = ShippingRate::getNewInstance($service, 0, 10000000);
		$shippingRate->flatCharge->set(100);
		$shippingRate->save();

		$product = $this->products[0];
		$this->order->addProduct($product, 1, false);
		$this->order->save();

		// set shipping rate
		$shipment = $this->order->getShipments()->get(0);
		$rates = $this->order->getDeliveryZone()->getShippingRates($shipment);
		$shipment->setAvailableRates($rates);
		$shipment->setRateId($rates->get(0)->getServiceID());
		$shipment->save();

		$this->order->finalize($this->usd);
		$this->assertEquals($this->order->getTotal($this->usd), (100 * 1.2) + (100 * 1.5));
	}

	public function testTwoTaxes()
	{
		$tax = Tax::getNewInstance('GST');
		$tax->save();
		$tax2 = Tax::getNewInstance('PST');
		$tax2->save();

		// shipment delivery zone
		$zone = DeliveryZone::getNewInstance();
		$zone->name->set('Canada');
		$zone->save();
		$country = DeliveryZoneCountry::getNewInstance($zone, 'US');
		$country->save();

		// taxes
		TaxRate::getNewInstance($zone, $tax, 10)->save();
		TaxRate::getNewInstance($zone, $tax2, 15)->save();

		// shipping rates
		$service = ShippingService::getNewInstance($zone, 'def', ShippingService::SUBTOTAL_BASED);
		$service->save();
		$shippingRate = ShippingRate::getNewInstance($service, 0, 10000000);
		$shippingRate->flatCharge->set(100);
		$shippingRate->save();

		// order
		$product = $this->products[0];
		$this->order->addProduct($product, 1, false);
		$this->order->save();

		// set shipping rate
		$shipment = $this->order->getShipments()->get(0);
		$rates = $this->order->getDeliveryZone()->getShippingRates($shipment);
		$shipment->setAvailableRates($rates);
		$shipment->setRateId($rates->get(0)->getServiceID());
		$shipment->save();

		$this->order->finalize($this->usd);
		$this->assertEquals($this->order->getTotal($this->usd), 200 * 1.10 * 1.15);
	}

	public function testTaxAmountChange()
	{
		$tax = Tax::getNewInstance('GST');
		$tax->save();

		// shipment delivery zone
		$zone = DeliveryZone::getNewInstance();
		$zone->name->set('Canada');
		$zone->save();
		$country = DeliveryZoneCountry::getNewInstance($zone, 'US');
		$country->save();

		// taxes
		TaxRate::getNewInstance($zone, $tax, 10)->save();

		$this->order->save(true);

		$shipment = Shipment::getNewInstance($this->order);
		$shipment->save();
		$this->order->addShipment($shipment);

		$item = $this->order->addProduct($this->products[0], 1, false, $shipment);
		$shipment->recalculateAmounts();
		$this->order->save();
		$this->assertEqual($shipment->taxAmount->get(), 10);

		$this->order->updateCount($item, 2);
		$shipment->recalculateAmounts();
		$this->assertEqual($shipment->taxAmount->get(), 20);

		$this->order->save();
		$shipment->save();

		// there should only be one ShipmentTax instance for this shipment
		$this->assertEqual($shipment->getRelatedRecordSet('ShipmentTax')->size(), 1);

		// reload order and add more items
		ActiveRecord::clearPool();
		$order = CustomerOrder::getInstanceByID($this->order->getID(), true);
		$order->loadAll();

		$shipment = $order->getShipments()->get(0);
		$order->addProduct($this->products[1], 1, false, $shipment);

		$shipment->recalculateAmounts();
		$shipment->save();
		$order->save();
		$this->order->finalize($this->usd);

		$this->assertEqual($shipment->getRelatedRecordSet('ShipmentTax')->size(), 1);
		$this->assertEqual($shipment->getRelatedRecordSet('ShipmentTax')->get(0)->amount->get(), 40);
	}
}

?>