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
}

?>