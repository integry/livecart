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
		$zone->isEnabled->set(true);
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
		$zone->isEnabled->set(true);
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

		$this->order->finalize();
		$this->assertEquals($this->order->getTotal(), (100 * 1.2) + (100 * 1.5));
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
		$zone->isEnabled->set(true);
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

		$this->order->finalize();
		$this->assertEquals($this->order->getTotal(), 200 * 1.10 * 1.15);
	}

	public function testTaxAmountChange()
	{
		$tax = Tax::getNewInstance('GST');
		$tax->save();

		// shipment delivery zone
		$zone = DeliveryZone::getNewInstance();
		$zone->name->set('Canada');
		$zone->isEnabled->set(true);
		$zone->save();
		$country = DeliveryZoneCountry::getNewInstance($zone, 'US');
		$country->save();

		// taxes
		TaxRate::getNewInstance($zone, $tax, 10)->save();

		$this->order->save(true);

		$shipment = Shipment::getNewInstance($this->order);
		$shipment->save();
		$this->order->addShipment($shipment);

		$item = $this->order->addProduct($this->products[0], 1, false, $shipment); // $100
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
		$order->addProduct($this->products[1], 1, false, $shipment); // $200

		$shipment->recalculateAmounts(false);
		$shipment->save();

		// @todo: fix failing assertion
		$this->assertEquals(1, $shipment->getRelatedRecordSet('ShipmentTax')->size(), 'expecting one ShipmentTax');

		$order->save();
		$this->order->finalize();

		/* debug
		echo "\n Order is finalized!\n";
		foreach($shipment->getRelatedRecordSet('ShipmentTax') as $item)
		{
			echo
				'shipment tax id:', $item->getID(),
				', type:', $item->type->get(),
				', amount:', $item->getAmount(), ' ('. 	$item->shipmentID->get()->getTaxAmount() .')',
				', shipment id:', $item->shipmentID->get()->getID(), 
				', taxRate id:', $item->taxRateID->get()->getID(),
				', taxClass:', implode(';', $item->taxRateID->get()->taxID->get()->name->get()).'('. $item->taxRateID->get()->taxID->get()->getID() .')',
				', zone:',  $item->taxRateID->get()->deliveryZoneID->get()->name->get(),'(', $item->taxRateID->get()->deliveryZoneID->get()->getID() ,')', // blame canada!!
				"\n";
		}
		*/
		$this->assertEquals(1, $order->getShipments()->size(), 'expecting one shipment');
		$this->assertEqual(40 , $shipment->getRelatedRecordSet('ShipmentTax')->get(0)->shipmentID->get()->getTaxAmount(), 40);
		
		// @todo: fix failing assertion
		$this->assertEquals(1, $shipment->getRelatedRecordSet('ShipmentTax')->size(), 'expecting one ShipmentTax');
		$this->assertEqual($shipment->getRelatedRecordSet('ShipmentTax')->get(0)->amount->get(), 40);
	}

	/**
	 *  Calculate taxes for shipments sent inside Quebec
	 */
	public function testQuebecToQuebecTaxes()
	{
		$gst = Tax::getNewInstance('GST');
		$gst->save();
		$pst = Tax::getNewInstance('PST');
		$pst->save();

		$this->assertTrue($pst->includesTax($gst));

		// shipment delivery zone
		$zone = DeliveryZone::getNewInstance();
		$zone->name->set('Canada');
		$zone->isEnabled->set(true);
		$zone->save();
		DeliveryZoneCountry::getNewInstance($zone, 'US')->save();

		// taxes
		TaxRate::getNewInstance($zone, $gst, 5)->save();
		$pstRate = TaxRate::getNewInstance($zone, $pst, 7.5);
		$pstRate->save();
		$pstRate->reload();

		// there have been some problems with saving/retrieving floats, so these would be caught here..
		$this->assertEquals(7.5, $pstRate->rate->get());

		// shipping rates
		$service = ShippingService::getNewInstance($zone, 'def', ShippingService::SUBTOTAL_BASED);
		$service->save();
		$shippingRate = ShippingRate::getNewInstance($service, 0, 10000000);
		$shippingRate->flatCharge->set(16.95);
		$shippingRate->save();

		// order
		$product = $this->products[0];
		$product->setPrice('USD', 50);
		$this->order->addProduct($product, 1, false);
		$this->order->save();

		// set shipping rate
		$shipment = $this->order->getShipments()->get(0);
		$rates = $this->order->getDeliveryZone()->getShippingRates($shipment);
		$shipment->setAvailableRates($rates);
		$shipment->setRateId($rates->get(0)->getServiceID());
		$shipment->save();

		$this->order->finalize();

		$this->assertEquals($this->order->shipments->get(0)->shippingAmount->get(), 16.95);

		$expectedTotal = round(50 * 1.05 * 1.075, 2) + round(16.95 * 1.05 * 1.075, 2);
		$this->assertEquals($this->order->getTotal(true), $expectedTotal);

		// sum taxes with base prices
		$orderArray = $this->order->toArray();
		$sum = 50 + 16.95;
		foreach ($orderArray['taxes']['USD'] as $tax)
		{
			$sum += $tax['amount'];
		}

		$this->assertEquals($this->order->getTotal(), $sum);
	}

	public function testQuebecToCanadaTaxes()
	{
		$gst = Tax::getNewInstance('GST');
		$gst->save();
		$pst = Tax::getNewInstance('PST');
		$pst->save();

		// shipment delivery zone
		$zone = DeliveryZone::getNewInstance();
		$zone->name->set('Quebec');
		$zone->isEnabled->set(true);
		$zone->save();

		// two taxes are applied to delivery charge
		TaxRate::getNewInstance($zone, $gst, 5)->save();
		TaxRate::getNewInstance($zone, $pst, 7.5)->save();

		$delZone = $zone;

		$this->config->set('DELIVERY_TAX', $zone->getID());

		// shipping amount zone
		$zone = DeliveryZone::getNewInstance();
		$zone->name->set('Canada');
		$zone->isEnabled->set(true);
		$zone->save();

		$service = ShippingService::getNewInstance($zone, 'def', ShippingService::SUBTOTAL_BASED);
		$service->save();

		DeliveryZoneCountry::getNewInstance($zone, 'US')->save();

		// but only one tax to item price
		TaxRate::getNewInstance($zone, $gst, 5)->save();

		$shippingRate = ShippingRate::getNewInstance($service, 0, 10000000);
		$shippingRate->flatCharge->set(16.95);
		$shippingRate->save();

		$product = $this->products[0];
		$product->setPrice('USD', 50);
		$this->order->addProduct($product, 1, false);
		$this->order->save();

		// set shipping rate
		$shipment = $this->order->getShipments()->get(0);
		$rates = $this->order->getDeliveryZone()->getShippingRates($shipment);
		$shipment->setAvailableRates($rates);
		$shipment->setRateId($rates->get(0)->getServiceID());
		$shipment->save();

		$this->order->finalize();
		$this->assertSame($this->order->getDeliveryZone(), $zone);
		$this->assertEquals($this->order->getTotal(), 71.63);
	}

	public function testQuebecToUSATaxes()
	{
		$gst = Tax::getNewInstance('GST');
		$gst->save();
		$pst = Tax::getNewInstance('PST');
		$pst->save();

		// shipment delivery zone
		$zone = DeliveryZone::getNewInstance();
		$zone->name->set('Quebec');
		$zone->isEnabled->set(true);
		$zone->save();

		// two taxes are applied to delivery charge
		TaxRate::getNewInstance($zone, $gst, 5)->save();
		TaxRate::getNewInstance($zone, $pst, 7.5)->save();

		$delZone = $zone;

		$this->config->set('DELIVERY_TAX', $zone->getID());

		// shipping amount zone
		$zone = DeliveryZone::getNewInstance();
		$zone->name->set('Canada');
		$zone->isEnabled->set(true);
		$zone->save();

		$service = ShippingService::getNewInstance($zone, 'def', ShippingService::SUBTOTAL_BASED);
		$service->save();

		DeliveryZoneCountry::getNewInstance($zone, 'US')->save();

		$shippingRate = ShippingRate::getNewInstance($service, 0, 10000000);
		$shippingRate->flatCharge->set(16.95);
		$shippingRate->save();

		$product = $this->products[0];
		$product->setPrice('USD', 50);
		$this->order->addProduct($product, 1, false);
		$this->order->save();

		// set shipping rate
		$shipment = $this->order->getShipments()->get(0);
		$rates = $this->order->getDeliveryZone()->getShippingRates($shipment);
		$shipment->setAvailableRates($rates);
		$shipment->setRateId($rates->get(0)->getServiceID());
		$shipment->save();

		$this->order->finalize();
		$this->assertSame($this->order->getDeliveryZone(), $zone);
		$this->assertEquals($this->order->getTotal(), 69.13);
	}

	public function testDefaultZoneShipping()
	{
		$tax = Tax::getNewInstance('VAT');
		$tax->save();

		// shipment delivery zone
		$zone = DeliveryZone::getDefaultZoneInstance();

		$taxRate = TaxRate::getNewInstance($zone, $tax, 19);
		$taxRate->save();

		$service = ShippingService::getNewInstance($zone, 'def', ShippingService::SUBTOTAL_BASED);
		$service->save();

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

		$this->order->finalize();
		$this->assertEquals(round($this->order->getSubTotal(), 2), round(100 / 1.19, 2));
		$this->assertEquals($this->order->getShipments()->get(0)->getTotal(), 100 + 100);
		$this->assertEquals($this->order->getTotal(), 100 + 100);
	}

	public function testNetherlandsTax()
	{
		$tax = Tax::getNewInstance('VAT');
		$tax->save();

		// shipment delivery zone
		$zone = DeliveryZone::getDefaultZoneInstance();

		$taxRate = TaxRate::getNewInstance($zone, $tax, 21);
		$taxRate->save();

		$service = ShippingService::getNewInstance($zone, 'def', ShippingService::SUBTOTAL_BASED);
		$service->save();

		$shippingRate = ShippingRate::getNewInstance($service, 0, 10000000);
		$shippingRate->flatCharge->set(6);
		$shippingRate->save();

		$product = $this->products[0];
		$product->setPrice('USD', 24.70);
		$product->save();

		$this->order->addProduct($product, 4, false);
		$this->order->save();

		// set shipping rate
		$shipment = $this->order->getShipments()->get(0);
		$rates = $this->order->getDeliveryZone()->getShippingRates($shipment);

		$shipment->setAvailableRates($rates);
		$shipment->setRateId($rates->get(0)->getServiceID());
		$shipment->save();

		$this->order->finalize();
		$this->assertEquals($this->order->getTotal(), 104.80);
	}

	public function testTaxRounding()
	{
		$tax = Tax::getNewInstance('VAT');
		$tax->save();

		TaxRate::getNewInstance(DeliveryZone::getDefaultZoneInstance(), $tax, 19)->save();

		foreach (array(2 => true, 1 => false) as $shipments => $isSeparate)
		{
			$this->initOrder();
			foreach (array(635.99, 228.69, 61.59) as $key => $price)
			{
				$this->products[$key]->setPrice('USD', $price);

				if (!$isSeparate)
				{
					$this->products[$key]->isSeparateShipment->set(false);
				}

				$this->order->addProduct($this->products[$key], 1, false);
			}

			$this->order->save();

			$this->assertEquals(count($this->order->getShipments()), $shipments);
			$this->assertEquals($this->order->getTotal(), 926.27);
		}
	}
}

?>