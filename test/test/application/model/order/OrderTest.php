<?php

require_once dirname(__FILE__) . '/OrderTestCommon.php';

/**
 *	Test Order model
 *
 *  @author Integry Systems
 *  @package test.model.order
 */
class OrderTest extends OrderTestCommon
{
	function testAddingToAndRemovingFromCart()
	{
		$this->order->addProduct($this->products[0], 1);
		$this->order->addProduct($this->products[0], 0);
		$this->assertEqual($this->order->getSubTotal($this->usd), 0);

		$this->order->addProduct($this->products[0], 1);
		$this->order->addProduct($this->products[0], -1);
		$this->assertEqual($this->order->getSubTotal($this->usd), 0);
	}

	function testSubTotal()
	{
		$subtotal = 0;
		foreach ($this->products as $product)
		{
			$this->order->addProduct($product, 1);
			$subtotal += $product->getPrice('USD');
		}
		$this->assertEqual($this->order->getSubTotal($this->usd), $subtotal);
	}

	function testShipments()
	{
		foreach ($this->products as $product)
		{
			$this->order->addProduct($product, 1);
		}

		$this->assertEqual($this->order->getShipments()->size(), 2);
	}

/*
	function XtestSerialization()
	{
		$rates = new ShippingRateSet();

		$rate = new ShipmentDeliveryRate();
		$rate->setServiceID(12);
		$rate->setCost(33, 'USD');
		$rates->add($rate);

		$rate = new ShipmentDeliveryRate();
		$rate->setServiceID(14);
		$rate->setCost(53, 'USD');
		$rates->add($rate);

		$shipments = $this->order->getShipments();

		foreach ($shipments as $shipment)
		{
			$shipment->setAvailableRates($rates);
		}

		$subTotal = $this->order->getSubTotal($this->usd);

		// make sure none of the old objects are used after unserialization
		ActiveRecord::clearPool();

		$this->order = unserialize(serialize($this->order));

		$this->assertEqual($subTotal, $this->order->getSubTotal($this->usd));

		$this->assertEqual($shipments->size(), $this->order->getShipments()->size());
		$this->assertEqual(count($shipments->get(0)->getItems()), count($this->order->getShipments()->get(0)->getItems()));
	}
*/

	function testFinalize()
	{
		$this->order->addProduct($this->products[0], 1);
		$this->order->addProduct($this->products[1], 1);
		$this->order->save();

		$this->order->finalize();
		$total = $this->order->getTotal(true);

		// the sum of all shipments amounts should be equal to the order amount
		$sum = 0;
		foreach ($this->order->getShipments() as $shipment)
		{
			$sum += $shipment->amount;
		}

		$this->assertEqual($sum, $this->order->totalAmount);

		ActiveRecord::clearPool();

		// reload the whole order data - the calculated total should still match
		$order = CustomerOrder::getInstanceById($this->order->getID(), true);
		$order->loadAll();
		$this->assertEqual($total, $order->getTotal(true));

		// change price for one product...
		foreach ($order->getShoppingCartItems() as $item)
		{
			$product = $item->product;
			$product->setPrice('USD', $product->getPrice('USD') + 10);
//			$order->removeProduct($product);
//			var_dump(count($order->getShoppingCartItems()));
			$order->save();
//			var_dump(count($order->getShoppingCartItems()));
//			$order->save();
//			var_dump(count($order->getShoppingCartItems()));
//			var_dump($order->totalAmount . '!');
//			var_dump('test');
//			$order->addProduct($product, 1);
			$order->save();
//			var_dump('test');
//			var_dump(count($order->getShoppingCartItems()));
//			var_dump($order->getShoppingCartItems());
			break;
		}

//		var_dump($order->getTotal(true));

		// ...so the new total calculated total would be different
		// $this->assertNotEqual($total, $order->getTotal(true));

		// however the "closed" price should still be the same as this order is already finalized
		$this->assertEqual($total, $order->totalAmount);
	}

	function testPayment()
	{
		$this->order->addProduct($this->products[0], 1);
		$this->order->addProduct($this->products[1], 1);
		$this->order->save();

		$this->order->finalize();

		$result = new TransactionResult();
		$result->amount->set($this->order->totalAmount);
		$result->currency->set($this->order->currency->getID());
		$result->gatewayTransactionID->set('TESTTRANSACTION');
		$result->setTransactionType(TransactionResult::TYPE_SALE);

		$transaction = Transaction::getNewInstance($this->order, $result);
		$transaction->save();

		$this->assertEqual($this->order->totalAmount, $this->order->capturedAmount);
	}

	function testMerge()
	{
		$order =  CustomerOrder::getNewInstance($this->user);
		$second = CustomerOrder::getNewInstance($this->user);

		$order->addProduct($this->products[0], 1);
		$second->addProduct($this->products[1], 1);

		$order->merge($second);

		$this->assertEqual(count($order->getOrderedItems()), 2);

		$order->save();
		$order->finalize();

		$second->save();

		// empty orders (without items) should not be saved
		$this->assertNull($second->getID());

		ActiveRecord::clearPool();

		$order = CustomerOrder::getInstanceById($order->getID());
		$order->loadAll();
		$this->assertEqual(count($order->getOrderedItems()), 2);
	}

	public function testMergeItemsWithOptions()
	{
		$product = $this->products[0];
		$option = ProductOption::getNewInstance($product);
		$option->save();
		$choice1 = ProductOptionChoice::getNewInstance($option);
		$choice1->save();
		$choice2 = ProductOptionChoice::getNewInstance($option);
		$choice2->save();

		$order = CustomerOrder::getNewInstance($this->user);
		$item = $order->addProduct($product, 1);
		$this->assertIsA($item, 'OrderedItem');
		$this->assertEqual(count($order->getShoppingCartItemCount()), 1);

		$order->addProduct($product, 1);
		$this->assertEqual(count($order->getItemsByProduct($product)), 2);
		$this->assertEqual($order->getShoppingCartItemCount(), 2);

		// merge without options
		$order->mergeItems();
		$this->assertEqual(count($order->getItemsByProduct($product)), 1);
		$this->assertEqual($order->getShoppingCartItemCount(), 2);

		// merge with options
		$item = array_shift($order->getItemsByProduct($product));
		$item->count->set(1);
		$item->addOptionChoice($choice1);

		$item2 = $order->addProduct($product, 1);
		$item2->addOptionChoice($choice2);

		$order->mergeItems();
		$this->assertEqual(count($order->getItemsByProduct($product)), 2);
		$this->assertEqual($order->getShoppingCartItemCount(), 2);

		// set same options
		$item2->removeOptionChoice($choice2);
		$item2->addOptionChoice($choice1);

		$order->mergeItems();
		$this->assertEqual(count($order->getItemsByProduct($product)), 1);
		$this->assertEqual($order->getShoppingCartItemCount(), 2);
	}

	function testUpdateCounts()
	{
		$product = $this->products[0];
		$order = CustomerOrder::getNewInstance($this->user);

		// allow fractional units
		$product->isFractionalUnit->set(true);
		$order->addProduct($product, 1.5);
		$items = $order->getItemsByProduct($product);
		$this->assertEqual($items[0]->count, 1.5);

		// disable fractional units
		$product->isFractionalUnit->set(false);
		$order->updateCount($items[0], 1.2);
		$this->assertEqual($items[0]->count, 1);

		$order->removeProduct($product);
		$order->addProduct($product, 3.3);
		$items = $order->getItemsByProduct($product);
		$this->assertEqual($items[0]->count, 3);
	}

	function testDigitalItems()
	{
		$order = CustomerOrder::getNewInstance($this->user);

		$price = 400;

		$product = Product::getNewInstance(Category::getInstanceById(Category::ROOT_ID), 'test3');
		$product->save();
		$product->setPrice('USD', $price);
		$product->type->set(Product::TYPE_DOWNLOADABLE);
		$product->isEnabled->set(true);
		$product->save();

		$order->addProduct($product, 1);
		$order->save();

		$this->assertEqual($order->getSubTotal($this->usd), $price);

		$order->finalize();
		$this->assertEqual($order->getSubTotal($this->usd), $price);

		ActiveRecord::clearPool();

		$loadedOrder = CustomerOrder::getInstanceById($order->getID());
		$loadedOrder->loadAll();
		$this->assertEqual($loadedOrder->getSubTotal($this->usd), $price);

		// check created shipments
		$this->assertEqual($loadedOrder->getShipments()->size(), 1);
		$this->assertTrue($loadedOrder->getShipments()->get(0)->getID() > 0);
	}

	function testDigitalItemsAddedThroughShipment()
	{
		$order = CustomerOrder::getNewInstance($this->user);

		$price = 400;

		$product = Product::getNewInstance(Category::getInstanceById(Category::ROOT_ID), 'test3');
		$product->save();
		$product->setPrice('USD', $price);
		$product->type->set(Product::TYPE_DOWNLOADABLE);
		$product->isEnabled->set(true);
		$product->save();

		$order->addProduct($product, 1);

		$item = array_shift($order->getItemsByProduct($product));

		$shipment = Shipment::getNewInstance($order);
		$shipment->addItem($item);

		$order->save();
		$shipment->recalculateAmounts();
		$shipment->save();
		$order->save();
		$order->finalize();

		$this->assertEqual($order->getSubTotal($this->usd), $price);

		ActiveRecord::clearPool();

		$loadedOrder = CustomerOrder::getInstanceById($order->getID());
		$loadedOrder->loadAll();
		$this->assertEqual($loadedOrder->getSubTotal($this->usd), $price);
	}

	public function testOrderTotalAmountWithTaxesAndDeliveryZone()
	{
		// create delivery zone/tax environment
		$zone = DeliveryZone::getNewInstance();
		$zone->isEnabled->set(true);
		$this->createOrderWithZone($zone);

		$this->order->addProduct($this->products[0]);
		$this->order->save();

		// make sure the correct delivery zone is used
		$this->assertSame($this->order->getDeliveryZone(), $zone);

		// calculate expected costs
		$itemPrice = $this->products[0]->getPrice($this->usd);
		$itemPriceWithTax = $itemPrice * 1.2;
		$shippingWithTax = 120;
		$total = $shippingWithTax + $itemPriceWithTax;
		$tax = 40; // (100 + 100) * 0.2

		$shipment = $this->order->getShipments()->get(0);
		$this->assertEqual($shipment->getTotal(true), $itemPriceWithTax);

		$rates = $zone->getShippingRates($shipment);
		$shipment->setAvailableRates($rates);
		$shipment->setRateId($rates->get(0)->getServiceID());
		$shipment->save();

		$this->assertEqual($this->order->getSubTotalBeforeTax($this->usd), $total - $tax);
		$this->assertEqual($this->order->getTotal(true), $total);
		$this->order->save();

		ActiveRecord::clearPool();
		$order = CustomerOrder::getInstanceByID($this->order->getID(), true);
		$order->loadAll();

		$this->assertEqual($order->getShipments()->get(0)->getTaxAmount($this->usd), $tax);

		$order->finalize();

		$this->assertEqual($order->getShipments()->get(0)->getTaxAmount($this->usd), $tax);

		$this->assertEqual($order->getTotal(true), $total);

		ActiveRecord::clearPool();
		$order = CustomerOrder::getInstanceByID($this->order->getID());
		$order->loadAll();
		$this->assertEqual($order->getTotal(true), $total);
	}

	public function testShippingClasses()
	{
		// set up classes
		$books = ShippingClass::getNewInstance('Books');
		$books->save();

		$cds = ShippingClass::getNewInstance('CDs');
		$cds->save();

		$shoes = ShippingClass::getNewInstance('Shoes');
		$shoes->save();

		$zone = DeliveryZone::getDefaultZoneInstance();

		$service = ShippingService::getNewInstance($zone, 'def', ShippingService::SUBTOTAL_BASED);
		$service->save();

		$shippingRate = ShippingRate::getNewInstance($service, 0, 10000000);

		// no rate is being set for the Shoes class, so it should use the default one
		$shippingRate->perItemCharge->set(100);
		$shippingRate->setClassItemCharge($books, 10);
		$shippingRate->setClassItemCharge($cds, 5);
		$shippingRate->save();

		$this->products[0]->shippingClass->set($shoes);
		$this->products[1]->shippingClass->set($books);
		$this->products[2]->shippingClass->set($cds);

		for ($k = 0; $k <= 2; $k++)
		{
			$this->products[$k]->isSeparateShipment->set(false);
			$this->order->addProduct($this->products[$k], 1, false);
		}

		$this->order->save();

		// set shipping rate
		$shipment = $this->order->getShipments()->get(0);
		$rates = $this->order->getDeliveryZone()->getShippingRates($shipment);
		$shipment->setAvailableRates($rates);
		$shipment->setRateId($rates->get(0)->getServiceID());
		$shipment->save();

		$this->assertEquals($rates->get(0)->getAmountByCurrency($this->usd), 115);
	}

	public function testInventory()
	{
		$this->config->setRuntime('INVENTORY_TRACKING', 'ENABLE_AND_HIDE');

		$product = $this->products[0];
		$product->stockCount->set(2);
		$product->save();

		$order = CustomerOrder::getNewInstance($this->user);
		$order->addProduct($product, 1);
		$order->save();
		$order->finalize();

		$product->reload();
		$this->assertEqual($product->stockCount, 1);
		$this->assertEqual($product->reservedCount, 1);

		// mark order as shipped - the stock is gone
		$order->setStatus(CustomerOrder::STATUS_SHIPPED);
		foreach ($order->getShipments() as $shipment)
		{
			$this->assertEqual($shipment->status, Shipment::STATUS_SHIPPED);
		}

		$this->assertEqual($product->stockCount, 1);
		$this->assertEqual($product->reservedCount, 0);
	}

	public function testInventoryForCancelledorderBy()
	{
		$this->config->setRuntime('INVENTORY_TRACKING', 'ENABLE_AND_HIDE');

		$product = $this->products[0];
		$product->stockCount->set(2);
		$product->save();

		$order = CustomerOrder::getNewInstance($this->user);
		$order->addProduct($product, 1);
		$order->save();
		$order->finalize();

		$this->assertEqual($product->stockCount, 1);
		$order->cancel();

		$product->reload();
		$this->assertEqual($product->stockCount, 2);
		$this->assertEqual($product->reservedCount, 0);
	}

	public function testInventoryForRestoredorderBy()
	{
		$this->config->setRuntime('INVENTORY_TRACKING', 'ENABLE_AND_HIDE');

		$product = $this->products[0];
		$product->stockCount->set(2);
		$product->save();

		$order = CustomerOrder::getNewInstance($this->user);
		$item = $order->addProduct($product, 1);
		$order->save();
		$order->finalize();

		$order->setStatus(CustomerOrder::STATUS_SHIPPED);
		$product->reload();
		$this->assertEqual($product->reservedCount, 0);
		$this->assertEqual($product->stockCount, 1);

		$order->setStatus(CustomerOrder::STATUS_RETURNED);
		$product->reload();
		$this->assertEqual($item->reservedProductCount, 1);
		$this->assertEqual($product->reservedCount, 1);
		$this->assertEqual($product->stockCount, 1);

		$order->cancel();
		$product->reload();
		$this->assertEqual($item->reservedProductCount, 0);
		$this->assertEqual($product->stockCount, 2);
		$this->assertEqual($product->reservedCount, 0);
	}

	public function testInventoryForReturnedorderBy()
	{
		$this->config->setRuntime('INVENTORY_TRACKING', 'ENABLE_AND_HIDE');

		$product = $this->products[0];
		$product->stockCount->set(2);
		$product->save();

		$order = CustomerOrder::getNewInstance($this->user);
		$order->addProduct($product, 1);
		$order->save();
		$order->finalize();

		$product->reload();
		$this->assertEqual($product->stockCount, 1);

		$order->cancel();

		$product->reload();
		$this->assertEqual($product->stockCount, 2);

		$order->restore();

		$product->reload();
		$this->assertEqual($product->stockCount, 1);
		$this->assertEqual($product->reservedCount, 1);
	}

	public function testInventoryForChangedorderBy()
	{
		$this->config->setRuntime('INVENTORY_TRACKING', 'ENABLE_AND_HIDE');

		$product = $this->products[0];
		$product->stockCount->set(2);
		$product->save();

		$second = $this->products[1];
		$second->stockCount->set(2);
		$second->save();

		$order = CustomerOrder::getNewInstance($this->user);
		$item = $order->addProduct($product, 1);
		$order->save();
		$order->finalize();

		$i = $order->addProduct($second, 1, null, $item->shipment);
		$this->assertEqual($i->shipment->getID(), $item->shipment->getID());
		$order->save();
		$this->assertEqual($i->shipment->getID(), $item->shipment->getID());
		$this->assertEqual(count($item->shipment->getItems()), 2);

		$second->reload();
		$this->assertEqual($second->stockCount, 1);
		$this->assertEqual($second->reservedCount, 1);

		$i->count->set(2);
		$i->save();
		$this->assertEqual($second->stockCount, 0);
		$this->assertEqual($second->reservedCount, 2);

		$order->setStatus(CustomerOrder::STATUS_SHIPPED);
		$this->assertEqual($second->stockCount, 0);
		$this->assertEqual($second->reservedCount, 0);

		$order->setStatus(CustomerOrder::STATUS_RETURNED);
		$this->assertEqual($second->stockCount, 0);
		$this->assertEqual($second->reservedCount, 2);

		$order->setStatus(CustomerOrder::STATUS_SHIPPED);
		$this->assertEqual($second->stockCount, 0);
		$this->assertEqual($second->reservedCount, 0);

		// stock levels won't change if a shipped order is cancelled
		$order->cancel();
		$this->assertEqual($second->stockCount, 0);
		$this->assertEqual($second->reservedCount, 0);
	}

	public function testInventoryForChangedProduct()
	{
		$this->config->setRuntime('INVENTORY_TRACKING', 'ENABLE_AND_HIDE');

		$product = $this->products[0];
		$product->stockCount->set(2);
		$product->save();

		$second = $this->products[1];
		$second->stockCount->set(2);
		$second->save();

		$order = CustomerOrder::getNewInstance($this->user);
		$item = $order->addProduct($product, 1);
		$order->save();
		$order->finalize();

		$item->product->set($second);
		$item->save();
		$order->save();

		$product->reload();
		$this->assertEqual($product->stockCount, 2);
		$this->assertEqual($product->reservedCount, 0);

		$second->reload();
		$this->assertEqual($second->stockCount, 1);
		$this->assertEqual($second->reservedCount, 1);
	}

	public function testInventoryForDownloadableProducts()
	{
		$this->config->setRuntime('INVENTORY_TRACKING', 'ENABLE_AND_HIDE');
		$this->config->setRuntime('INVENTORY_TRACKING_DOWNLOADABLE', false);

		$product = $this->products[0];
		$product->stockCount->set(2);
		$product->type->set(Product::TYPE_DOWNLOADABLE);
		$product->save();

		$order = CustomerOrder::getNewInstance($this->user);
		$order->addProduct($product, 1);
		$order->save();
		$order->finalize();

		$product->reload();
		$this->assertEqual($product->stockCount, 2);
		$this->assertEqual((int)$product->reservedCount, 0);

		$order->setStatus(CustomerOrder::STATUS_SHIPPED);
		foreach ($order->getShipments() as $shipment)
		{
			$this->assertEqual($shipment->status, Shipment::STATUS_SHIPPED);
		}

		$this->assertEqual($product->stockCount, 2);
		$this->assertEqual((int)$product->reservedCount, 0);
	}

	public function testEnabledInventoryTrackingForDownloadableProducts()
	{
		$this->config->setRuntime('INVENTORY_TRACKING', 'ENABLE_AND_HIDE');
		$this->config->setRuntime('INVENTORY_TRACKING_DOWNLOADABLE', true);

		$product = $this->products[0];
		$product->stockCount->set(2);
		$product->type->set(Product::TYPE_DOWNLOADABLE);
		$product->save();

		$order = CustomerOrder::getNewInstance($this->user);
		$order->addProduct($product, 1);
		$order->save();
		$order->finalize();

		$product->reload();
		$this->assertEqual($product->stockCount, 1);
		$this->assertEqual($product->reservedCount, 1);

		// mark order as shipped - the stock is gone
		$order->setStatus(CustomerOrder::STATUS_SHIPPED);
		foreach ($order->getShipments() as $shipment)
		{
			$this->assertEqual($shipment->status, Shipment::STATUS_SHIPPED);
		}

		$this->assertEqual($product->stockCount, 1);
		$this->assertEqual($product->reservedCount, 0);

		$this->assertEqual($product->getMaxOrderableCount(), 1);
	}

	public function testUpdatingToStock()
	{
		$this->config->setRuntime('INVENTORY_TRACKING', 'ENABLE_AND_HIDE');

		$product = $this->products[0];
		$product->stockCount->set(2);
		$product->save();

		$second = $this->products[1];
		$second->stockCount->set(2);
		$second->save();

		$order = CustomerOrder::getNewInstance($this->user);
		$item = $order->addProduct($product, 2);
		$item2 = $order->addProduct($second, 2);
		$order->save();

		// no changes made yet - return nothing
		$this->assertEqual(count($order->updateToStock()), 0);

		$product->stockCount->set(1);
		$second->stockCount->set(0);
		$result = $order->updateToStock();

		// quantity of the first item should be reduced to 1 and the second item should be removed
		$this->assertEqual(count($result), 2);
		$this->assertEqual($item->count, 1);
		$this->assertEqual((int)$item->isSavedForLater, OrderedItem::CART);
		$this->assertEqual($item2->isSavedForLater, OrderedItem::OUT_OF_STOCK);

		// no changes made after update - return nothing
		$this->assertEqual(count($order->updateToStock()), 0);

		// second item back in stock
		$second->stockCount->set(2);
		$result = $order->updateToStock();
		$this->assertEqual(count($result), 1);
		$this->assertEqual((int)$item->isSavedForLater, OrderedItem::CART);
		$this->assertEqual($item2->isSavedForLater, OrderedItem::CART);
	}

	public function testOrderingABundle()
	{
		$container = Product::getNewInstance(Category::getRootNode());
		$container->isEnabled->set(true);
		$container->type->set(Product::TYPE_BUNDLE);
		$container->setPrice($this->usd, 100);
		$container->save();

		foreach ($this->products as $key => $product)
		{
			$inst = ProductBundle::getNewInstance($container, $product);
			$inst->count->set($key + 1);
			$inst->save();
		}

		$this->assertTrue($container->isAvailable());

		foreach ($this->products as $key => $product)
		{
			$product->stockCount->set($key + 2);
			$product->save();
		}

		$this->config->setRuntime('INVENTORY_TRACKING', 'ENABLE_AND_HIDE');

		$this->assertTrue($container->isAvailable());

		$order = CustomerOrder::getNewInstance($this->user);
		$order->addProduct($container, 1);
		$order->save();

		$this->assertEqual($order->getShoppingCartItemCount(), 1);

		$order->finalize();

		$this->assertEqual(count($order->getOrderedItems()), 1);
		$this->assertEqual(count($order->getShoppingCartItems()), 1);
		$this->assertEqual($order->getShoppingCartItemCount(), 1);
		$this->assertEqual($order->getTotal(true), 100);

		$containerItem = array_shift($order->getItemsByProduct($container));
		$this->assertSame($containerItem->product, $container);

		$subItems = $containerItem->getSubItems();
		$this->assertEqual($subItems->size(), count($this->products));

		// the sub-items should never show up in the order product list
		ActiveRecord::clearPool();
		$reloaded = CustomerOrder::getInstanceByID($order->getID());
		$reloaded->loadItems();
		$this->assertEqual(count($reloaded->getOrderedItems()), 1);
		$this->assertEqual(count(array_shift($reloaded->getOrderedItems())->getSubItems()), 3);

/*
		>> Inventory is now deducted on finalization instead of when an order is shipped <<

		// check inventory
		foreach ($this->products as $product)
		{
			$this->assertEqual($product->reservedCount, 1);
			$this->assertEqual($product->stockCount, 2);
		}
*/
		// mark order as shipped - the stock is gone
		$this->assertNotEquals($order->status, CustomerOrder::STATUS_SHIPPED);

		$reloaded->setStatus(CustomerOrder::STATUS_SHIPPED);

		foreach ($reloaded->getShipments() as $shipment)
		{
			$this->assertEqual($shipment->status, Shipment::STATUS_SHIPPED);
		}

		foreach ($this->products as $key => $product)
		{
			$product->reload();
			$this->assertEqual($product->reservedCount, 0);
			$this->assertEqual($product->stockCount, 1);
		}

		$this->config->setRuntime('INVENTORY_TRACKING', 'DISABLE');
	}

	public function testDownloadableBundle()
	{
		$container = Product::getNewInstance(Category::getRootNode());
		$container->isEnabled->set(true);
		$container->type->set(Product::TYPE_BUNDLE);
		$container->setPrice($this->usd, 100);
		$container->save();

		foreach ($this->products as $product)
		{
			$product->type->set(Product::TYPE_DOWNLOADABLE);
			ProductBundle::getNewInstance($container, $product)->save();
		}

		$order = CustomerOrder::getNewInstance($this->user);
		$order->addProduct($container, 1);
		$order->save();
		$order->finalize();

		$this->assertEqual($order->getShipments()->size(), 1);
		$this->assertFalse($order->getShipments()->get(0)->isShippable());
	}

	public function testFixedDiscountWithoutTaxAndShipping()
	{
		$this->order->addProduct($this->products[0]);
		$this->order->getShipments();
		$this->order->save();

		// before finalizing
		$total = $this->order->getTotal(true);

		$discount = OrderDiscount::getNewInstance($this->order);
		$discount->amount->set(10);
		$discount->save();

		$this->assertEquals($this->order->getTotal(true), $total - 10);
		$this->order->save();

		// finalized
		$this->order->finalize();
		$this->assertEquals($this->order->getTotal(true), $total - 10);

		// reload order
		ActiveRecordModel::clearPool();
		$order = CustomerOrder::getInstanceByID($this->order->getID());
		$order->loadAll();
		$this->assertEquals($order->getTotal(true), $total - 10);

		// modify reloaded order
		$newTotal = $this->products[0]->getPrice($this->usd) + $this->products[1]->getPrice($this->usd);
		$order->addProduct($this->products[1]);
		$order->save();
		$this->assertEquals($order->getTotal(true), $newTotal - 10);
	}

	public function testFixedDiscountWithTaxesAndDeliveryZone()
	{
		$this->createOrderWithZone();

		$this->order->addProduct($this->products[0]);
		$this->order->save();

		$shipment = $this->order->getShipments()->get(0);
		$rates = $this->order->getDeliveryZone()->getShippingRates($shipment);
		$shipment->setAvailableRates($rates);
		$shipment->setRateId($rates->get(0)->getServiceID());
		$shipment->save();

		$initTotal = $this->order->getTotal(true);
		$initTax = $this->order->getTaxAmount();

		$discount = OrderDiscount::getNewInstance($this->order);
		$discount->amount->set(10);
		$discount->save();

		$this->assertEquals((int)$this->order->getTotal(true), (int)($initTotal - 10));
		$tax = $this->order->getTaxAmount();

		$expectedTax = (110 / 6) + 20;
		$this->assertEquals(round($expectedTax, 2), round($tax, 2));
	}

	public function testSimpleItemDiscount()
	{
		$condition = DiscountCondition::getNewInstance();
		$condition->isEnabled->set(true);
		$condition->conditionClass->set('RuleConditionContainsProduct');
		$condition->save();

		$record = DiscountConditionRecord::getNewInstance($condition, $this->products[0]);
		$record->save();
		$condition->loadAll();

		$action = DiscountAction::getNewInstance($condition, 'RuleActionPercentageDiscount');
		$action->isEnabled->set(true);
		$action->type->set(DiscountAction::TYPE_ITEM_DISCOUNT);
		$action->amount->set(10);
		$action->save();

		$this->order->addProduct($this->products[0]);
		$this->order->addProduct($this->products[1]);
		$this->order->save();

		// order wide 10% discount on all items
		$this->assertEquals(count($this->order->getDiscountConditions(true)), 1);
		$originalTotal = $this->products[0]->getPrice($this->usd) + $this->products[1]->getPrice($this->usd);
		$this->assertEquals($this->order->getTotal(true), $originalTotal * 0.9);

		// discount applied on the same items that matched the rules
		$action->actionCondition->set($condition);
		$action->save();
		$this->order->processBusinessRules(true);

		$this->assertTrue(RuleCondition::create($condition)->isProductMatching($this->products[0]));
		$this->assertFalse(RuleCondition::create($condition)->isProductMatching($this->products[1]));
		$expectedTotal = ($this->products[0]->getPrice($this->usd) * 0.9) + $this->products[1]->getPrice($this->usd);
		$this->assertEquals($this->order->getTotal(true), $expectedTotal);

		// apply discount to the second item as well
		$record = DiscountConditionRecord::getNewInstance($condition, $this->products[1]);
		$record->save();
		$condition->loadAll();
		$this->assertEquals($this->order->getTotal(true), $originalTotal * 0.9);
	}

	public function testDiscountFinalize()
	{
		$condition = DiscountCondition::getNewInstance();
		$condition->isEnabled->set(true);
		$condition->conditionClass->set('RuleConditionContainsProduct');
		$condition->save();

		$record = DiscountConditionRecord::getNewInstance($condition, $this->products[0]);
		$record->save();
		$condition->loadAll();

		$action = DiscountAction::getNewInstance($condition);
		$action->isEnabled->set(true);
		$action->type->set(DiscountAction::TYPE_ITEM_DISCOUNT);
		$action->amount->set(10);
		$action->actionClass->set('RuleActionPercentageDiscount');
		$action->save();

		$this->order->addProduct($this->products[0]);
		$this->order->addProduct($this->products[1]);
		$this->order->save();

		$this->assertEquals(count($this->order->getDiscountActions()), 1);

		// test order total
		$total = $this->order->getTotal(true);
		$this->order->finalize();
		$this->assertEquals($this->order->getTotal(), $total);

		ActiveRecordModel::clearPool();
		$order = CustomerOrder::getInstanceById($this->order->getID(), true);
		$order->loadAll();
		$this->assertEquals($order->getTotal(true), $total);
		$this->assertEquals(count($order->getDiscountActions()), 1);

		// test item prices
		$item = array_shift($order->getItemsByProduct($this->products[0]));
		//var_dump($item->price);


	}

	public function testApplyingTwoDiscountsToOneItemAndOneToOther()
	{
		for ($k = 1; $k <= 2; $k++)
		{
			$cond[$k] = DiscountCondition::getNewInstance();
			$cond[$k]->isEnabled->set(true);
			$cond[$k]->conditionClass->set('RuleConditionContainsProduct');
			$cond[$k]->save();

			$action[$k] = DiscountAction::getNewInstance($cond[$k]);
			$action[$k]->isEnabled->set(true);
			$action[$k]->type->set(DiscountAction::TYPE_ITEM_DISCOUNT);
			$action[$k]->amount->set(10 * $k);
			$action[$k]->actionClass->set('RuleActionPercentageDiscount');
			$action[$k]->save();
		}

		$this->order->addProduct($this->products[0]);
		$this->order->addProduct($this->products[1]);
		$this->order->save();

		// order wide 28% discount (cumulative 10% and 20% discount) on all items
		$originalTotal = $this->products[0]->getPrice($this->usd) + $this->products[1]->getPrice($this->usd);
		$this->assertEquals($this->order->getTotal(true), $originalTotal * 0.72);

		// apply 20% discount to both items, and 10% discount to first item only
		$record = DiscountConditionRecord::getNewInstance($cond[1], $this->products[0]);
		$record->save();
		$cond[1]->loadAll();
		$this->assertFalse(RuleCondition::create($cond[1])->isProductMatching($this->products[1]));

		$action[1]->actionCondition->set($cond[1]);
		$action[1]->save();

		$expectedTotal = ($this->products[0]->getPrice($this->usd) * 0.9 * 0.8) + ($this->products[1]->getPrice($this->usd) * 0.8);
		$this->assertEquals($this->order->getTotal(true), $expectedTotal);
	}

	public function testDiscountPriority()
	{
		foreach (array('RuleActionFixedDiscount' => 1, 'RuleActionPercentageDiscount' => 2) as $class => $k)
		{
			$cond[$k] = DiscountCondition::getNewInstance();
			$cond[$k]->isEnabled->set(true);
			$cond[$k]->save();

			$action[$k] = DiscountAction::getNewInstance($cond[$k]);
			$action[$k]->isEnabled->set(true);
			$action[$k]->type->set(DiscountAction::TYPE_ITEM_DISCOUNT);
			$action[$k]->amount->set(10 * $k);
			$action[$k]->actionClass->set($class);
			$action[$k]->save();
		}

		$price0 = $this->products[0]->getPrice($this->usd);
		$price1 = $this->products[1]->getPrice($this->usd);
		$total = $price0 + $price1;

		$this->order->addProduct($this->products[0]);
		$this->order->addProduct($this->products[1]);
		$this->order->save();

		$expectedTotal = $total1 = (($price0 - 10) * 0.8) + (($price1 - 10) * 0.8);
		$this->assertEquals($this->order->getTotal(true), $expectedTotal);

		// switch discount priorities
		$cond[1]->position->set(1);
		$cond[1]->save();
		$cond[2]->position->set(0);
		$cond[2]->save();

		$this->order->getDiscountActions(true);
		$expectedTotal = $total2 = (($price0 * 0.8) - 10) + (($price1 * 0.8) - 10);
		$this->assertEquals($this->order->getTotal(true), $expectedTotal);

		$cond[1]->isEnabled->set(false);
		$cond[1]->save();
		$cond[2]->isEnabled->set(false);
		$cond[2]->save();

		// two actions, one condition
		$condition = DiscountCondition::getNewInstance();
		$condition->isEnabled->set(true);
		$condition->save();
		$action[1]->condition->set($condition);
		$action[1]->save();
		$action[2]->condition->set($condition);
		$action[2]->save();
		$this->assertEquals($this->order->getTotal(true), $total1);

		// switch action priorities
		$action[1]->position->set(1);
		$action[1]->save();
		$action[2]->position->set(0);
		$action[2]->save();
		$this->assertEquals($this->order->getTotal(true), $total2);
	}

	public function testDisabledDiscountConditions()
	{
		foreach (array('RuleActionFixedDiscount' => 1, 'RuleActionPercentageDiscount' => 2) as $class => $k)
		{
			$cond[$k] = DiscountCondition::getNewInstance();
			$cond[$k]->isEnabled->set($k == 1);
			$cond[$k]->save();

			$action[$k] = DiscountAction::getNewInstance($cond[$k]);
			$action[$k]->isEnabled->set(true);
			$action[$k]->type->set(DiscountAction::TYPE_ITEM_DISCOUNT);
			$action[$k]->amount->set(10 * $k);
			$action[$k]->actionClass->set($class);
			$action[$k]->save();
		}

		$price0 = $this->products[0]->getPrice($this->usd);
		$price1 = $this->products[1]->getPrice($this->usd);
		$total = $price0 + $price1;

		$this->order->addProduct($this->products[0]);
		$this->order->addProduct($this->products[1]);
		$this->order->save();

		$this->assertEquals($this->order->getTotal(true), $total - 20);

		// add a second action
		$act = DiscountAction::getNewInstance($cond[1]);
		$act->isEnabled->set(true);
		$act->type->set(DiscountAction::TYPE_ITEM_DISCOUNT);
		$act->amount->set(30);
		$act->actionClass->set('RuleActionFixedDiscount'); // 0 - percent, 1 - amount
		$act->save();
		$cond[1]->loadAll();

		$this->order->getDiscountActions(true);
		$this->assertEquals($this->order->getTotal(true), $total - 20 - 60);

		// and disable it
		$act->isEnabled->set(false);
		$act->save();

		$this->order->getDiscountActions(true);
		$this->assertEquals($this->order->getTotal(true), $total - 20);
	}

	public function testFixedDiscountWithItemDiscount()
	{
		// order wide discount
		$condition = DiscountCondition::getNewInstance();
		$condition->isEnabled->set(true);
		$condition->save();

		$action = DiscountAction::getNewInstance($condition);
		$action->isEnabled->set(true);
		$action->type->set(DiscountAction::TYPE_ORDER_DISCOUNT);
		$action->amount->set(10);
		$action->actionClass->set('RuleActionFixedDiscount');
		$action->save();

		// item discount
		$condition = DiscountCondition::getNewInstance();
		$condition->conditionClass->set('RuleConditionContainsProduct');
		$condition->isEnabled->set(true);
		$condition->save();

		$record = DiscountConditionRecord::getNewInstance($condition, $this->products[0]);
		$record->save();

		$action = DiscountAction::getNewInstance($condition);
		$action->actionCondition->set($condition);
		$action->isEnabled->set(true);
		$action->type->set(DiscountAction::TYPE_ITEM_DISCOUNT);
		$action->amount->set(10);
		$action->actionClass->set('RuleActionPercentageDiscount');
		$action->save();

		$condition->loadAll();

		$this->order->addProduct($this->products[0]);
		$this->order->addProduct($this->products[1]);
		$this->order->save();

		$this->assertEquals(count($this->order->getDiscountConditions()), 2);
		$this->assertEquals(count($this->order->getDiscountActions(true)), 2);

		$expectedTotal = ($this->products[0]->getPrice($this->usd) * 0.9) + $this->products[1]->getPrice($this->usd) - 10;
		$this->assertEquals($this->order->getTotal(true), $expectedTotal);

		$this->order->finalize();
		$this->assertEquals($this->order->getTotal(), $expectedTotal);
//		$this->assertEquals($this->order->getTotal(true), $expectedTotal);
	}

	public function testDiscountForSomeItemsIfCertainNumberOfOtherItemsAreInCart()
	{
		// order condition
		$condition = DiscountCondition::getNewInstance();
		$condition->isEnabled->set(true);
		$condition->conditionClass->set('RuleConditionContainsProduct');
		$condition->save();
		$record = DiscountConditionRecord::getNewInstance($condition, $this->products[0]);
		$record->save();

		// action condition
		$actionCondition = DiscountCondition::getNewInstance();
		$actionCondition->isActionCondition->set(true);
		$actionCondition->isEnabled->set(true);
		$actionCondition->conditionClass->set('RuleConditionContainsProduct');
		$actionCondition->save();
		$record = DiscountConditionRecord::getNewInstance($actionCondition, $this->products[1]);
		$record->save();

		$action = DiscountAction::getNewInstance($condition);
		$action->actionCondition->set($actionCondition);
		$action->isEnabled->set(true);
		$action->type->set(DiscountAction::TYPE_ITEM_DISCOUNT);
		$action->amount->set(10);
		$action->actionClass->set('RuleActionPercentageDiscount');
		$action->save();

		$this->order->addProduct($this->products[0], 3);
		$this->order->addProduct($this->products[1], 2);
		$this->order->save();

		$this->assertEquals(count($this->order->getDiscountConditions()), 1);
		$this->assertEquals(count($this->order->getDiscountActions(true)), 1);

		$price0 = $this->products[0]->getPrice($this->usd);
		$price1 = $this->products[1]->getPrice($this->usd);

		$expectedTotal = ($price1 * 0.9 * 2) + ($price0 * 3);
		$this->assertEquals($expectedTotal, $this->order->getTotal(true));

		// require at least 4 items of products[0], but we have only 3 in cart, so no discount
		$condition->comparisonType->set(DiscountCondition::COMPARE_GTEQ);
		$condition->count->set(4);
		$condition->save();

		$this->assertEquals(count($this->order->getDiscountActions(true)), 0);
		$normalPrice = ($price1 * 2) + ($price0 * 3);

		$this->assertEquals($normalPrice, $this->order->getTotal(true));

		// require at least 3 items of products[0], so this should pass
		$condition->count->set(3);
		$condition->save();
		$this->order->getDiscountActions(true);
		$this->assertEquals($expectedTotal, $this->order->getTotal(true));

		// require less than 5 items of products[0] - pass
		$condition->count->set(5);
		$condition->comparisonType->set(DiscountCondition::COMPARE_LTEQ);
		$condition->save();
		$this->order->getDiscountActions(true);
		$this->assertEquals($expectedTotal, $this->order->getTotal(true));

		// require less than 2 items of products[0] - no discount
		$condition->count->set(2);
		$condition->comparisonType->set(DiscountCondition::COMPARE_LTEQ);
		$condition->save();
		$this->order->getDiscountActions(true);
		$this->assertEquals($normalPrice, $this->order->getTotal(true));

		// require exactly 7 items of products[0] - no discount
		$condition->count->set(7);
		$condition->comparisonType->set(DiscountCondition::COMPARE_EQ);
		$condition->save();
		$this->assertEquals(count($this->order->getDiscountActions(true)), 0);
		$this->assertEquals($normalPrice, $this->order->getTotal(true));

		// require exactly 3 items of products[0] - pass
		$condition->count->set(3);
		$condition->comparisonType->set(DiscountCondition::COMPARE_EQ);
		$condition->save();
		$this->assertEquals(count($this->order->getDiscountActions(true)), 1);
		$this->assertEquals($expectedTotal, $this->order->getTotal(true));

		// require count other than 2 items of products[0] - pass
		$condition->count->set(2);
		$condition->comparisonType->set(DiscountCondition::COMPARE_NE);
		$condition->save();
		$this->order->getDiscountActions(true);
		$this->assertEquals($expectedTotal, $this->order->getTotal(true));

		// require count other than 3 items of products[0] - no discount
		$condition->count->set(3);
		$condition->comparisonType->set(DiscountCondition::COMPARE_NE);
		$condition->save();
		$this->assertEquals(count($this->order->getDiscountActions(true)), 0);
		$this->assertEquals($normalPrice, $this->order->getTotal(true));
	}

	public function testDiscountForManufacturerProducts()
	{
		$manufacturer = Manufacturer::getNewInstance('Discount Test');
		$manufacturer->save();
		$this->products[0]->manufacturer->set($manufacturer);
		$this->products[0]->save();

		// order condition
		$condition = DiscountCondition::getNewInstance();
		$condition->isEnabled->set(true);
		$condition->count->set(4);
		$condition->comparisonType->set(DiscountCondition::COMPARE_GTEQ);
		$condition->conditionClass->set('RuleConditionContainsProduct');
		$condition->save();
		$record = DiscountConditionRecord::getNewInstance($condition, $manufacturer);
		$record->save();

		$action = DiscountAction::getNewInstance($condition);
		$action->actionCondition->set($condition);
		$action->isEnabled->set(true);
		$action->type->set(DiscountAction::TYPE_ITEM_DISCOUNT);
		$action->amount->set(10);
		$action->actionClass->set('RuleActionPercentageDiscount');
		$action->save();

		$this->order->addProduct($this->products[0], 3);
		$this->order->addProduct($this->products[1], 2);
		$this->order->save();

		$price0 = $this->products[0]->getPrice($this->usd);
		$price1 = $this->products[1]->getPrice($this->usd);
		$expectedTotal = ($price1 * 2) + ($price0 * 0.9 * 3);
		$normalPrice = ($price1 * 2) + ($price0 * 3);

		$this->assertEquals($normalPrice, $this->order->getTotal(true));

		// require only 3 items of this manufacturer
		$condition->count->set(3);
		$condition->save();

		$this->assertEquals(count($this->order->getDiscountActions(true)), 1);
		$this->assertEquals($expectedTotal, $this->order->getTotal(true));
	}

	public function testDiscountForCategoryProducts()
	{
		$category = Category::getNewInstance(Category::getRootNode());
		$category->save();
		$newProduct = Product::getNewInstance($category);
		$newProduct->isEnabled->set(true);
		$newProduct->setPrice($this->usd, 100);
		$newProduct->save();

		Category::recalculateProductsCount();
		$newProduct->reload();

		// order condition
		$condition = DiscountCondition::getNewInstance();
		$condition->isEnabled->set(true);
		$condition->count->set(4);
		$condition->comparisonType->set(DiscountCondition::COMPARE_GTEQ);
		$condition->conditionClass->set('RuleConditionContainsProduct');
		$condition->save();
		$record = DiscountConditionRecord::getNewInstance($condition, $category);
		$record->save();

		$action = DiscountAction::getNewInstance($condition);
		$action->actionCondition->set($condition);
		$action->isEnabled->set(true);
		$action->type->set(DiscountAction::TYPE_ITEM_DISCOUNT);
		$action->amount->set(10);
		$action->actionClass->set('RuleActionPercentageDiscount');
		$action->save();

		$this->order->addProduct($this->products[0], 3, true);
		$this->order->addProduct($newProduct, 2, true);
		$this->order->save();

		$price0 = $this->products[0]->getPrice($this->usd);
		$price1 = $newProduct->getPrice($this->usd);
		$expectedTotal = ($price0 * 3) + ($price1 * 0.9 * 2);
		$normalPrice = ($price0 * 3) + ($price1 * 2);

		$this->assertEquals($normalPrice, $this->order->getTotal(true));

		// require only 2 items of this category
		$condition->count->set(2);
		$condition->save();

		$this->assertEquals(count($this->order->getDiscountActions(true)), 1);
		$this->assertEquals($expectedTotal, $this->order->getTotal(true));
	}

	public function testDiscountForCategoryProductsBySubTotal()
	{
		$category = Category::getNewInstance(Category::getRootNode());
		$category->save();
		$newProduct = Product::getNewInstance($category);
		$newProduct->isEnabled->set(true);
		$newProduct->setPrice($this->usd, 100);
		$newProduct->save();

		Category::recalculateProductsCount();
		$newProduct->reload();

		// order condition
		$condition = DiscountCondition::getNewInstance();
		$condition->isEnabled->set(true);
		$condition->subTotal->set(300);
		$condition->comparisonType->set(DiscountCondition::COMPARE_GTEQ);
		$condition->conditionClass->set('RuleConditionContainsProduct');
		$condition->save();
		$record = DiscountConditionRecord::getNewInstance($condition, $category);
		$record->save();

		$action = DiscountAction::getNewInstance($condition);
		$action->actionCondition->set($condition);
		$action->isEnabled->set(true);
		$action->type->set(DiscountAction::TYPE_ITEM_DISCOUNT);
		$action->amount->set(10);
		$action->actionClass->set('RuleActionPercentageDiscount');
		$action->save();

		$this->order->addProduct($this->products[0], 3, true);
		$this->order->addProduct($newProduct, 2, true);
		$this->order->save();

		$price0 = $this->products[0]->getPrice($this->usd);
		$price1 = $newProduct->getPrice($this->usd);
		$expectedTotal = ($price0 * 3) + ($price1 * 0.9 * 2);
		$normalPrice = ($price0 * 3) + ($price1 * 2);

		$this->assertEquals($normalPrice, $this->order->getTotal(true));

		// require subtotal to be at least 150 (we have 200)
		$condition->subTotal->set(150);
		$condition->save();

		$this->assertEquals(count($this->order->getDiscountActions(true)), 1);
		$this->assertEquals($expectedTotal, $this->order->getTotal(true));
	}

	public function testDiscountByAdditionalCategories()
	{
		$product = $this->products[1];

		$condition = DiscountCondition::getNewInstance();
		$condition->isEnabled->set(true);
		$condition->save();

		$actionCondition = DiscountCondition::getNewInstance();
		$actionCondition->isEnabled->set(true);
		$actionCondition->conditionClass->set('RuleConditionContainsProduct');
		$actionCondition->save();

		$action = DiscountAction::getNewInstance($condition);
		$action->actionCondition->set($actionCondition);
		$action->isEnabled->set(true);
		$action->type->set(DiscountAction::TYPE_ITEM_DISCOUNT);
		$action->amount->set(10);
		$action->actionClass->set('RuleActionPercentageDiscount');
		$action->save();

		$randomCategory = Category::getNewInstance(Category::getRootNode());
		$randomCategory->save();
		DiscountConditionRecord::getNewInstance($actionCondition, $randomCategory)->save();

		$this->order->addProduct($product, 1, true);
		$this->order->save();

		Category::recalculateProductsCount();
		$product->reload();
		$this->assertFalse(RuleCondition::create($actionCondition)->isProductMatching($product));

		$customCategory = Category::getNewInstance(Category::getRootNode());
		$customCategory->save();
		ProductCategory::getNewInstance($product, $customCategory)->save();
		DiscountConditionRecord::getNewInstance($actionCondition, $customCategory)->save();

		Category::recalculateProductsCount();
		$product->reload();

		$actionCondition->loadAll();
		$this->assertTrue(RuleCondition::create($actionCondition)->isProductMatching($product));

		$this->assertEquals(count($this->order->getDiscountActions(true)), 1);
		$this->assertEquals($this->products[1]->getPrice($this->usd) * 0.9, $this->order->getTotal(true));

		ActiveRecordModel::clearPool();
		$order = CustomerOrder::getInstanceByID($this->order->getID());
		$order->loadAll();
		$this->assertEquals($this->products[1]->getPrice($this->usd) * 0.9, $this->order->getTotal(true));
	}

	public function testPaymentMethodSurcharge()
	{
		$condition = DiscountCondition::getNewInstance();
		$condition->isEnabled->set(true);
		$condition->conditionClass->set('RuleConditionPaymentMethodIs');
		$condition->addValue('TESTING');
		$condition->save();

		$action = DiscountAction::getNewInstance($condition, 'RuleActionPercentageSurcharge');
		$action->actionCondition->set($condition);
		$action->isEnabled->set(true);
		$action->type->set(DiscountAction::TYPE_ORDER_DISCOUNT);
		$action->amount->set(10);
		$action->save();

		$this->order->addProduct($this->products[1], 1, true);
		$this->order->setPaymentMethod('TESTING');
		$this->order->save();

		$price = $this->products[1]->getPrice($this->usd);
		$this->assertEquals((int)($price * 1.1), (int)$this->order->getTotal(true));

		$action->actionClass->set('RuleActionFixedDiscount');
		$action->save();
		$this->assertEquals((int)($price - 10), (int)$this->order->getTotal(true));

		$action->actionClass->set('RuleActionFixedSurcharge');
		$action->save();
		$this->assertEquals((int)($price + 10), (int)$this->order->getTotal(true));

		$action->actionClass->set('RuleActionPercentageDiscount');
		$action->save();
		$this->assertEquals((int)($price * 0.9), (int)$this->order->getTotal(true));
	}

	public function testQuantityPrices()
	{
		$product = $this->products[0];
		$this->order->addProduct($product, 5);

		$price = $product->getPricingHandler()->getPriceByCurrencyCode('USD');
		$this->assertIsA($price, 'ProductPrice');

		$price->setPriceRule(5, null, 15);
		$this->assertEquals($this->order->getTotal(true), 75);

		$price->removePriceRule(5, null);
		$this->assertEquals($this->order->getTotal(true), 500);

		$price->setPriceRule(4, null, 15);
		$this->assertEquals($this->order->getTotal(true), 75);

		$price->removePriceRule(4, null);
		$price->setPriceRule(6, null, 15);
		$this->assertEquals($this->order->getTotal(true), 500);

		$price->setPriceRule(9, null, 15);
		$this->assertEquals($this->order->getTotal(true), 500);

		// user group pricing
		$group = UserGroup::getNewInstance('test');
		$group->save();

		$price->setPriceRule(4, null, 15);
		$price->setPriceRule(4, $group, 10);
		$this->assertEquals($this->order->getTotal(true), 75);

		$user = $this->order->user;
		$user->userGroup->set($group);
		$user->save();
		$this->assertEquals($this->order->getTotal(true), 50);

		$price->removePriceRule(4, null);
		$price->removePriceRule(4, $group);

		$price->setPriceRule(4, null, 15);
		$price->setPriceRule(6, $group, 10);
		$this->assertEquals($this->order->getTotal(true), 75);

		$price->setPriceRule(5, $group, 10);
		$this->assertEquals($this->order->getTotal(true), 50);

		$price->setPriceRule(2, $group, 7);
		$price->setPriceRule(3, $group, 8);
		$price->setPriceRule(4, $group, 9);
		$price->setPriceRule(6, $group, 11);
		$this->assertEquals($this->order->getTotal(true), 50);
	}

	public function testShippingToMultipleAddresses()
	{
		$address1 = UserAddress::getNewInstance();
		$address1->countryID->set('US');
		$address1->save();

		$address2 = UserAddress::getNewInstance();
		$address2->countryID->set('CA');
		$address2->save();

		// zones, taxes and shipping rates
		$zone1 = DeliveryZone::getNewInstance();
		$zone1->isEnabled->set(true);
		$zone1->name->set('USA');
		$zone1->save();
		DeliveryZoneCountry::getNewInstance($zone1, 'US')->save();

		$zone2 = DeliveryZone::getNewInstance();
		$zone2->isEnabled->set(true);
		$zone2->name->set('Canada');
		$zone2->save();
		DeliveryZoneCountry::getNewInstance($zone2, 'CA')->save();

		$tax = Tax::getNewInstance('VAT');
		$tax->save();

		TaxRate::getNewInstance($zone1, $tax, 20)->save();
		TaxRate::getNewInstance($zone2, $tax, 15)->save();

		$service = ShippingService::getNewInstance($zone1, 'def1', ShippingService::SUBTOTAL_BASED);
		$service->save();
		$shippingRate = ShippingRate::getNewInstance($service, 0, 10000000);
		$shippingRate->flatCharge->set(100);
		$shippingRate->save();

		$service = ShippingService::getNewInstance($zone2, 'def2', ShippingService::SUBTOTAL_BASED);
		$service->save();
		$shippingRate = ShippingRate::getNewInstance($service, 0, 10000000);
		$shippingRate->flatCharge->set(78);
		$shippingRate->save();

		// set up order
		$this->order->isMultiAddress->set(true);
		$this->order->save(true);

		$product = $this->products[0];

		$shipment1 = Shipment::getNewInstance($this->order);
		$shipment1->shippingAddress->set($address1);
		$shipment1->save();

		$shipment2 = Shipment::getNewInstance($this->order);
		$shipment2->shippingAddress->set($address2);
		$shipment2->save();

		$this->order->addProduct($product, 1, true, $shipment1);
		$item = $this->order->addProduct($product, 1, true, $shipment2);

		// edit amount after saving just to make things more complicated
		$this->order->save();
		$item->count->set(2);

		// shipments shouldn't be reset like for regular orders
		$this->assertEquals($this->order->getShipments()->size(), 2);

		$price = $product->getPrice($this->usd);
		$this->assertEquals($this->order->getTotal(true), ($price * 1.2) + ($price * 2 * 1.15));

		// test if delivery zones are determined correctly
		$this->assertEqual($shipment1->getDeliveryZone()->getID(), $zone1->getID());

		// check if shipping rates are available
		$this->assertEqual($shipment1->getShippingRates()->size(), 1);
		$this->assertEqual($shipment1->getShippingRates()->get(0)->getCostAmount(), 100);
		$this->assertEqual($shipment2->getShippingRates()->get(0)->getCostAmount(), 78);

		foreach (array($shipment1, $shipment2) as $shipment)
		{
			$shipment->setRateId($shipment->getShippingRates()->get(0)->getServiceID());
			$shipment->recalculateAmounts();
			$shipment->save();
		}

		$this->order->save();
		$this->order->finalize();

		// reload order
		ActiveRecordModel::clearPool();
		$order = CustomerOrder::getInstanceById($this->order->getID(), true);
		$order->loadAll();

		$this->assertEquals($order->getShipments()->size(), 2);
		foreach ($order->getShipments() as $key => $shipment)
		{
			$this->assertEquals(count($shipment->getItems()), 1);
			$this->assertEquals(array_shift($shipment->getItems())->count, $key + 1);
		}

		// order total with taxes and shipping
		$this->assertEqual($order->getTotal(true), (($price + 100) * 1.2) + ((($price * 2) + 78) * 1.15));
	}

	public function testClone()
	{
		$this->order->isMultiAddress->set(true);
		$this->order->save(true);

		$shipment1 = Shipment::getNewInstance($this->order);
		$shipment1->save();

		$shipment2 = Shipment::getNewInstance($this->order);
		$shipment2->save();

		$this->order->addShipment($shipment1);
		$this->order->addShipment($shipment2);

		$this->order->addProduct($this->products[0], 1, true, $shipment1);
		$this->order->addProduct($this->products[1], 3, true, $shipment2);
		$this->assertEquals(2, $this->order->getShipments()->size());

		foreach ($this->order->getShipments() as $shipment)
		{
			$shipment->shippingAddress->set($this->user->defaultShippingAddress->userAddress);
		}

		$this->order->user->set($this->user);

		$this->order->save();
		$this->order->finalize();
		$total = $this->order->getTotal(true);

		ActiveRecord::clearPool();
		$reloaded = CustomerOrder::getInstanceByID($this->order->getID(), true);
		$reloaded->loadAll();

		$cloned = clone $reloaded;
		$cloned->save();

		$this->assertNotEquals($cloned->getID(), $this->order->getID());

		// check original order
		$this->assertEquals(2, $reloaded->getShipments()->size());
		$this->assertEquals(2, count($reloaded->getOrderedItems()));
		$this->assertEquals(1, array_shift($reloaded->getItemsByProduct($this->products[0]))->count);
		$this->assertEquals($total, $reloaded->getTotal(true));

		ActiveRecord::clearPool();
		$order = CustomerOrder::getInstanceByID($cloned->getID(), true);
		$order->loadAll();
		$order->currency->load();

		$this->assertTrue(is_object($this->user->defaultShippingAddress));

		//$this->user->reload();
		//$this->user->loadAddresses();

		$this->assertFalse((bool)$order->isFinalized);

		$this->assertTrue(is_object($this->user->defaultShippingAddress));

		$this->assertEquals($this->user->getID(), $order->user->getID());
		$this->assertEquals($order->billingAddress->getID(), $this->user->defaultBillingAddress->userAddress->getID());
		$this->assertEquals($order->shippingAddress->getID(), $this->user->defaultShippingAddress->userAddress->getID());

		foreach ($order->getShipments() as $shipment)
		{
			$this->assertEquals($shipment->shippingAddress->getID(),
								$this->user->defaultShippingAddress->userAddress->getID());
		}

		$this->assertEquals(2, count($order->getOrderedItems()));
		$this->assertEquals(2, $order->getShipments()->size());
		$this->assertEquals(1, count($order->getShipments()->get(0)->getItems()));
		$this->assertEquals(1, count($order->getShipments()->get(1)->getItems()));

		$item = array_shift($order->getShipments()->get(1)->getItems());
		$this->assertEquals(3, $item->count);
		$this->assertEquals($this->products[1]->getID(), $item->product->getID());

		// check the total of the original order
		ActiveRecord::clearPool();
		$reloaded = CustomerOrder::getInstanceByID($this->order->getID(), true);
		$reloaded->loadAll();
		$this->assertEquals($total, $reloaded->getTotal(true));
	}

	public function testVariationPricing()
	{
		$variation = $this->products[0]->createChildProduct();
		$variation->isEnabled->set(true);
		$variation->save();

		$this->order->addProduct($variation, 1, true);

		// override price
		$variation->setPrice('USD', 1);
		$this->assertEquals($this->order->getTotal(true), 1);

		// add to parent price
		$variation->setChildSetting('price', Product::CHILD_ADD);
		$this->assertEquals($this->order->getTotal(true), 101);

		// substract from parent price
		$variation->setChildSetting('price', Product::CHILD_SUBSTRACT);
		$this->assertEquals($this->order->getTotal(true), 99);

		// use parent price
		$variation->setPrice('USD', 0);
		$variation->setChildSetting('price', Product::CHILD_OVERRIDE);
		$variation->save();
		$this->assertEquals($this->order->getTotal(true), 100);
	}

	public function testReloadedVariationPricing()
	{
		$variation = $this->products[0]->createChildProduct();
		$variation->isEnabled->set(true);
		$variation->setChildSetting('price', Product::CHILD_OVERRIDE);
		$variation->setPrice('USD', 0);
		$variation->save();

		// clear pool and reload only variation instance
		ActiveRecordModel::clearPool();
		$reloadedVar = Product::getInstanceByID($variation->getID(), true);
		$order = CustomerOrder::getNewInstance($this->user);
		$order->addProduct($reloadedVar, 1, true);
		$this->assertEquals($order->getTotal(true), 100);
	}

	public function testInvoiceNumbers()
	{
		$config = $this->getConfig();
		$config->setRuntime('INVOICE_NUMBER_GENERATOR', 'SequentialInvoiceNumber');
		$config->setRuntime('SequentialInvoiceNumber_START_AT', '0');
		$config->setRuntime('SequentialInvoiceNumber_STEP', '1');
		$config->setRuntime('SequentialInvoiceNumber_MIN_LENGTH', '5');
		$config->setRuntime('SequentialInvoiceNumber_PREFIX', '');
		$config->setRuntime('SequentialInvoiceNumber_SUFFIX', '');

		$cart = clone $this->order;
		$second = clone $this->order;

		$this->order->addProduct($this->products[0], 1);
		$this->order->save();
		$this->order->finalize();

		$firstID = $this->order->invoiceNumber;
		$this->assertTrue(is_numeric($firstID));

		// create an unfinished order between two finished orders
		$cart->addProduct($this->products[0], 1);
		$cart->save();
		$this->assertNull($cart->invoiceNumber);

		$second->addProduct($this->products[0], 1);
		$second->save();
		$second->finalize();
		$this->assertEquals($firstID + 1, $second->invoiceNumber);
		$this->assertEquals($this->order->getID() + 2, $second->getID());
	}

	public function testInvoiceNumbersWithPrefixes()
	{
		$config = $this->getConfig();
		$config->setRuntime('INVOICE_NUMBER_GENERATOR', 'SequentialInvoiceNumber');
		$config->setRuntime('SequentialInvoiceNumber_START_AT', '50000');
		$config->setRuntime('SequentialInvoiceNumber_STEP', '5');
		$config->setRuntime('SequentialInvoiceNumber_MIN_LENGTH', '7');
		$config->setRuntime('SequentialInvoiceNumber_PREFIX', 'TEST');
		$config->setRuntime('SequentialInvoiceNumber_SUFFIX', '/2010');

		$cart = clone $this->order;
		$second = clone $this->order;

		$this->order->addProduct($this->products[0], 1);
		$this->order->save();
		$this->order->finalize();

		$firstID = $this->order->invoiceNumber;
		$this->assertEquals($firstID, 'TEST0050005/2010');

		// create an unfinished order between two finished orders
		$cart->addProduct($this->products[0], 1);
		$cart->save();
		$this->assertNull($cart->invoiceNumber);

		$second->addProduct($this->products[0], 1);
		$second->save();
		$second->finalize();
		$this->assertEquals($this->order->getID() + 2, $second->getID());
		$this->assertNotEquals($second->invoiceNumber, $firstID);
		$this->assertEquals($second->invoiceNumber, 'TEST0050010/2010');
	}

	public function testDeletedProductsInLiveOrders()
	{
		$this->order->addProduct($this->products[0], 1);
		$this->order->addProduct($this->products[1], 1);
		$this->order->save();
		$this->products[0]->delete();
		ActiveRecord::clearPool();
		$order = CustomerOrder::getInstanceByID($this->order->getID());
		$order->loadAll();

		$this->assertEqual($order->getShoppingCartItemCount(), 1);
	}

	public function testDeletedProductsInCompletedOrders()
	{
		$this->products[0]->setValueByLang('name', 'xx', 'test');
		$this->products[0]->save();
		$sku = $this->products[0]->sku;

		$this->order->addProduct($this->products[0], 1);
		$this->order->addProduct($this->products[1], 1);
		$this->order->save();
		$this->order->finalize();
		$total = $this->order->getTotal();
		$this->assertEqual($total, 300);

		$this->products[0]->delete();
		ActiveRecord::clearPool();

		$order = CustomerOrder::getInstanceByID($this->order->getID());
		$order->loadAll();

		$this->assertEqual($order->getShoppingCartItemCount(), 2);
		$this->assertEqual($total, $order->getTotal());

		$deletedItem = array_shift($order->getShoppingCartItems());
		$deleted = $deletedItem->toArray();
		$this->assertEqual($deleted['Product']['nameData']['xx'], 'test');
		$this->assertEqual($deleted['Product']['sku'], $sku);

		// change quantities
		$this->config->setRuntime('INVENTORY_TRACKING', 'ENABLE_AND_HIDE');
		$deletedItem->count->set(2);
		$order->save();

		$this->assertEqual(400, $order->getTotal());
	}

	public function testFreeShipping()
	{
		$this->createOrderWithZone();
		$this->newRate->delete();

		$product = $this->products[0];
		$product->isFreeShipping->set(true);
		$this->order->addProduct($product, 1);

		$this->assertSame($this->order->getDeliveryZone(), $this->newZone);

		$shipment = $this->order->getShipments()->get(0);

		$rates = $shipment->getShippingRates();
		$this->assertEqual($rates->size(), 0);

		$this->newZone->isFreeShipping->set(true);
		$this->assertEqual($shipment->getChargeableWeight(), 0);
		$rates = $shipment->getShippingRates();
		$this->assertEqual($rates->size(), 1);
	}

	public function testFindOrdersWithRecurringPeriodEndingToday_basic()
	{
		// one order, one invoice order (invoiceOrder.parentID = order.id)
		// + one order without type recurring
		// + one order with type recurring, but other generation date

		// config
		$config = ActiveRecordModel::getApplication()->getConfig();
		$config->set('RECURRING_BILLING_GENERATE_INVOICE', 3);
		$config->save();

		$order = CustomerOrder::getNewInstance($this->user);
		$order->save(true);
		$product = $this->products[0];
		$product->save();

		$rpp = $this->createRecurringProductPeriod($product, 50, RecurringProductPeriod::TYPE_PERIOD_DAY, 100);
		list($item, $recurringItem) = $this->addRecurringProduct($order, $product, 1, $rpp, 100, 200);
		$invoiceOrder1 = CustomerOrder::getNewInstance($this->user);
		$invoiceOrder1->parentID->set($order);
		$invoiceOrder1->startDate->set(date('Y-m-d 00:00:01', strtotime('+3 days', strtotime('-50 days'))));
		$invoiceOrder1->save(true);
		$recurringItem->saveLastInvoice($invoiceOrder1);

		$order2 = CustomerOrder::getNewInstance($this->user);
		$order2->addProduct($product, 1);
		$order2->save();

		$order3 = CustomerOrder::getNewInstance($this->user);
		$order3->save(true);
		list($item, $recurringItem) = $this->addRecurringProduct($order3, $product, 1, $rpp, 100, 200);
		$order3->dateCompleted->set(date('Y-m-d 00:00:02', strtotime('-7 days')));
		$order3->save();

		$orders = CustomerOrder::findOrdersWithRecurringPeriodEndingToday();

		$this->assertEquals(1, $orders->size());
	}

	public function testFindOrdersWithRecurringPeriodEndingToday_withoutInvoiceOrders()
	{
		// one order, no invoice orders

		// config
		$config = ActiveRecordModel::getApplication()->getConfig();
		$config->set('RECURRING_BILLING_GENERATE_INVOICE', 3);
		$config->save();

		$order = CustomerOrder::getNewInstance($this->user);
		$order->save(true);
		$product = $this->products[0];
		$product->save();
		$rpp = $this->createRecurringProductPeriod($product, 16, RecurringProductPeriod::TYPE_PERIOD_DAY, 100);
		list($item, $recurringItem) = $this->addRecurringProduct($order, $product, 1, $rpp, 100, 200);
		$order->startDate->set(date('Y-m-d 00:00:02', strtotime('+3 days', strtotime('-16 days'))));
		$order->save();
		$orders = CustomerOrder::findOrdersWithRecurringPeriodEndingToday();
		$this->assertEquals(1, $orders->size());
	}

	public function testGenerateInvoiceNumber()
	{
		$order = CustomerOrder::getNewInstance($this->user);
		$order->invoiceNumber->set('AD/S3[2-2]-25');
		$order->save(true);
		$this->assertEquals('AD/S3[2-2]-25', $order->getCalculatedRecurringInvoiceNumber(), 'Main order has no suffixes for invoiceNumber - should return the same');

		$invoiceOrder1 = CustomerOrder::getNewInstance($this->user);
		$invoiceOrder1->parentID->set($order);
		$invoiceOrder1->save(true);

		$this->assertEquals('AD/S3[2-2]-25-1', $invoiceOrder1->getCalculatedRecurringInvoiceNumber());

		$invoiceOrder2 = CustomerOrder::getNewInstance($this->user);
		$invoiceOrder2->parentID->set($order);
		$invoiceOrder2->save(true);

		$this->assertEquals('AD/S3[2-2]-25-2', $invoiceOrder2->getCalculatedRecurringInvoiceNumber());
	}

	public function testGenerateRecurringInvoices()
	{
		$config = ActiveRecordModel::getApplication()->getConfig();
		$config->set('RECURRING_BILLING_GENERATE_INVOICE', 3);
		$config->save();

		$order = CustomerOrder::getNewInstance($this->user);
		$order->save(true);
		$product = $this->products[0];
		$product->save();
		$rpp = $this->createRecurringProductPeriod($product, 1, RecurringProductPeriod::TYPE_PERIOD_YEAR, 100);

		list($item, $recurringItem) = $this->addRecurringProduct($order, $product, 1, $rpp, 100, 200);

		// what order startDate to set for invoice generation to occur today?
		/*
                     [ORDER]                                              [TODAY]
                        |                                                    |
                    <---+----------------- -1 year---------------------------+
            +3 days --->|                                                    |
                                                                                 
                        +-----------------  1 year ------------------------------+
                                                                             |<--- -3 days (need to generate 3 days before period start)
		*/
		$order->startDate->set(date('Y-m-d 00:00:02', strtotime('+3 days',strtotime('-1 year'))));
		$order->invoiceNumber->set('Recurring Order #1');
		$order->save();
		$orderID = $order->getID();

		$ids = CustomerOrder::generateRecurringInvoices();

		$this->assertEquals(1, count($ids), 'Should generate one invoice order');
		$filter = new ARSelectFilter();
		$filter->andWhere(new EqualsCond(new ARFieldHandle('CustomerOrder','parentID'), $orderID));
		$filter->andWhere(new EqualsCond(new ARFieldHandle('CustomerOrder','ID'), $ids[0]));
		$rs = ActiveRecordModel::getRecordSet('CustomerOrder', $filter);
		$this->assertEquals(1, $rs->size(), 'Should generate one invoice order');
		$invoice = $rs->shift();
		$this->assertEquals('Recurring Order #1-1', $invoice->invoiceNumber);
		$items = $invoice->getOrderedItems();
		$this->assertEquals(1, count($items), 'Invoice should have one OrderedItem');
		$item = array_shift($items);
		$this->assertEquals(200, $item->price, 'OrderedItem price should be set to period price');
	}

	public function testGenerateInvoices_shortScenario()
	{
		$config = ActiveRecordModel::getApplication()->getConfig();
		$config->set('RECURRING_BILLING_GENERATE_INVOICE', 3);
		$config->set('RECURRING_BILLING_PAYMENT_DUE_DATE_DAYS', 7);
		$config->save();

		// two products
		$product1 = $this->products[0];
		$product2 = $this->products[1];

		$price1 = 10.01;
		$price2 = 20.02;

		// with similar periods (length, type, rebill count match)
		$period1 = $this->createRecurringProductPeriod($product1, 15, RecurringProductPeriod::TYPE_PERIOD_DAY, 5);
		$period2 = $this->createRecurringProductPeriod($product2, 15, RecurringProductPeriod::TYPE_PERIOD_DAY, 5);

		// one empty order
		$order = CustomerOrder::getNewInstance($this->user);
		$order->save(true);

		// added products
		list($orderedItem1, $recurringItem1) = $this->addRecurringProduct(
			$order, $product1, 1, $period1, 0, $price1);

		list($orderedItem2, $recurringItem2) = $this->addRecurringProduct(
			$order, $product2, 1, $period2, 0, $price2);

		$order->save();
		$order->startDate->set('2010-01-01 00:00:00');
		$order->finalize();
		$order->save();

		$this->assertInvoices(array($order->getID()), array(
			'count'=>1,
			'totalPrice' => array(0, 'total setup price should be 0'),
			'rebillsLeft'=> array(5)
		));

		$generatedOrdersIDs = CustomerOrder::generateRecurringInvoices('2010-01-13');
		$this->assertInvoices($generatedOrdersIDs, array(
			'count'=>1,
			'totalPrice' => array($price1 + $price2, 'total price should be '.($price1 + $price2).' ('.$price1.' + '. $price2.')'),
			'rebillsLeft'=> array(4)
		));

		$generatedOrdersIDs = CustomerOrder::generateRecurringInvoices('2010-01-28');
		$this->assertInvoices($generatedOrdersIDs, array(
			'count'=>1,
			'totalPrice' => array($price1 + $price2, 'total price should be '.($price1 + $price2).' ('.$price1.' + '. $price2.')'),
			'rebillsLeft'=> array(3)
		));

		$generatedOrdersIDs = CustomerOrder::generateRecurringInvoices('2010-02-12');
		$this->assertInvoices($generatedOrdersIDs, array(
			'count'=>1,
			'totalPrice' => array($price1 + $price2, 'total price should be '.($price1 + $price2).' ('.$price1.' + '. $price2.')'),
			'rebillsLeft'=> array(2)
		));

		$generatedOrdersIDs = CustomerOrder::generateRecurringInvoices('2010-02-27');
		$this->assertInvoices($generatedOrdersIDs, array(
			'count'=>1,
			'totalPrice' => array($price1 + $price2, 'total price should be '.($price1 + $price2).' ('.$price1.' + '. $price2.')'),
			'rebillsLeft'=> array(1)
		));

		$generatedOrdersIDs = CustomerOrder::generateRecurringInvoices('2010-03-14');
		$this->assertInvoices($generatedOrdersIDs, array(
			'count'=>1,
			'totalPrice' => array($price1 + $price2, 'total price should be '.($price1 + $price2).' ('.$price1.' + '. $price2.')'),
			'rebillsLeft'=> array(0)
		));

		$this->assertIntervalHasNoInvoicesToGenerate('2010-03-15', '2011-03-15');
	}


	public function testGenerateInvoices_longScenario()
	{
		// configruation
		$config = ActiveRecordModel::getApplication()->getConfig();
		$config->set('RECURRING_BILLING_GENERATE_INVOICE', 3);
		$config->set('RECURRING_BILLING_PAYMENT_DUE_DATE_DAYS', 7);
		$config->save();

		// other orders (as some information noise).
		for($i=0; $i<3; $i++)
		{
			$order = CustomerOrder::getNewInstance($this->user);
			$product = $this->products[$i];
			$product->save();
			$order->addProduct($product, $i+2);
			$order->save();
			$order->finalize();
		}

		//..
		// order with multiple shipments, multiple recurring periods (everyting multiple)

		$period1 = $this->createRecurringProductPeriod($this->products[0], 15, RecurringProductPeriod::TYPE_PERIOD_DAY, null); // infinite rebill count
		$period2 = $this->createRecurringProductPeriod($this->products[1], 15, RecurringProductPeriod::TYPE_PERIOD_DAY, 2);
		$period3 = $this->createRecurringProductPeriod($this->products[2], 1, RecurringProductPeriod::TYPE_PERIOD_WEEK, 3); // every week, for 3 weeks

		$order = CustomerOrder::getNewInstance($this->user);
		$order->isMultiAddress->set(true);
		$order->save(true);

		$shipment1 = Shipment::getNewInstance($order);
		$shipment1->save();
		$shipment2 = Shipment::getNewInstance($order);
		$shipment2->save();
		$shipment3 = Shipment::getNewInstance($order);
		$shipment3->save();

		$order->addShipment($shipment1);
		$order->addShipment($shipment2);
		$order->addShipment($shipment3);

		list($orderedItem1, $recurringItem1) = $this->addRecurringProduct($order, $this->products[0], 1, $period1, 33.01, 50.01, $shipment1);
		list($orderedItem2, $recurringItem2) = $this->addRecurringProduct($order, $this->products[1], 3, $period2, 44.02, 100.02, $shipment2);
		list($orderedItem3, $recurringItem3) = $this->addRecurringProduct($order, $this->products[2], 2, $period3, 0, 60.03, $shipment3);
		$order->save();

		$order->startDate->set('2010-01-01 00:00:00');
		$order->finalize();
		
		// $order->dateCompleted->set('2010-01-01 00:00:01');
		$order->save();

		$this->assertInvoices(array($order->getID()), array(
			'count' => 1,
			'totalPrice' => array(33.01 + 3 * 44.02, 'Setup price should be '.(33.01 + 3 * 44.02).' (33.01 + 3 * 44.02)'),
			'rebillsLeft'=> array(-1) // ** cant determine rebill count because of different period lengths including period with infinite rebill count
		));

		// ** TIMELINE **

		// 2010-01-01 order created; period1 starts; period2 starts; period3 starts
		// 2010-01-02
		// 2010-01-03
		// 2010-01-04
		// 2010-01-05 3 days before period3 start
		// 2010-01-06 
		// 2010-01-07 
		// 2010-01-08 period3 starts
		// 2010-01-09
		// 2010-01-10
		// 2010-01-11
		// 2010-01-12 3 days before period3 start
		// 2010-01-13 3 days before period1 starts; 3 days before period2 starts
		// 2010-01-14
		// 2010-01-15 period3 starts
		// 2010-01-16 period 1 starts; period2 starts
		// 2010-01-17
		// 2010-01-18
		// 2010-01-19 3 days before period3 start
		// 2010-01-20
		// 2010-01-21
		// 2010-01-22 period3 starts
		// 2010-01-23
		// 2010-01-24
		// 2010-01-25
		// 2010-01-26 3 days before period3 start
		// 2010-01-27 3 days before period1 starts; 3 days before period2 starts
		// 2010-01-28
		// 2010-01-29 period3 starts
		// 2010-01-30
		// 2010-01-31 period 1 starts; period2 starts
		// 2010-02-01
		// 2010-02-02 3 days before period3 should start, but it has run out of rebills, should happen nothing.
		// 2010-02-03 
		// 2010-02-04 
		// 2010-02-05 period 3 rebill count expires
		// 2010-02-06
		// 2010-02-07
		// 2010-02-08
		// 2010-02-09
		// 2010-02-10
		// 2010-02-11
		// 2010-02-12
		// 2010-02-13
		// 2010-02-14
		// 2010-02-15
		// 2010-02-16 
		// 2010-02-17
		// 2010-02-18
		// 2010-02-19 
		// 2010-02-20
		// 2010-02-21
		// 2010-02-22
		// 2010-02-23
		// 2010-02-24
		// 2010-02-25
		// 2010-02-26
		// 2010-02-27
		// 2010-02-28
		// 2010-03-01

		$this->assertEquals(3, $order->getShipments()->size());
		$this->assertEquals(-1, $order->rebillsLeft);

		// from 2009-12-01 to 2010-01-04 there should be nothing to generate (see timeline above)
		$this->assertIntervalHasNoInvoicesToGenerate('2009-12-01', '2010-01-04');

		$generatedOrdersIDs = CustomerOrder::generateRecurringInvoices('2010-01-05');

		$this->assertInvoices($generatedOrdersIDs, array(
			'count' => array(1, 'Should generate invoice for period3 (3 days before period start)'),
			'periods' => array(
				array('2010-01-08', '2010-01-14')
			),
			'totalPrice' => 60.03 * 2,
			'rebillsLeft' => array(2)
		));

		$generatedOrdersIDs = CustomerOrder::generateRecurringInvoices('2010-01-05');
		$this->assertInvoices($generatedOrdersIDs, array(
			'count' => array(0, 'When called second time for same date should not generate more invoices')
		));

		$this->assertIntervalHasNoInvoicesToGenerate('2010-01-06', '2010-01-11');

		$generatedOrdersIDs = CustomerOrder::generateRecurringInvoices('2010-01-12');
		$this->assertInvoices($generatedOrdersIDs, array(
			'count' => array(1, '3 days before period 3 starts, should generate invoice'),
			'periods' => array(
				array('2010-01-15', '2010-01-21')
			),
			'rebillsLeft' => array(1)
		));

		$generatedOrdersIDs = CustomerOrder::generateRecurringInvoices('2010-01-13');
		$this->assertInvoices($generatedOrdersIDs, array(
			'count' => array(2, 'Should generate invoices for period1 and period2'), // todo merge?
			'orderedItemCount' => array(2, 'Should have 2 ordered items - one for product 1 other for product 2'),
			'totalPrice' => array(50.01 + 100.02 * 3, 'total price should be 50.01 + 100.02 * 3'),
			'periods' => array(
				array('2010-01-16', '2010-01-30'),
				array('2010-01-16', '2010-01-30')
			)
		));

		$generatedOrdersIDs = CustomerOrder::generateRecurringInvoices('2010-01-13');
		$this->assertInvoices($generatedOrdersIDs, array(
			'count' => array(0, 'When called second time for same date should not generate more invoices')
		));

		$this->assertIntervalHasNoInvoicesToGenerate('2010-01-14', '2010-01-18');

		$generatedOrdersIDs = CustomerOrder::generateRecurringInvoices('2010-01-13');
		$this->assertInvoices($generatedOrdersIDs, array(
			'count' => array(0, 'When called second time for same date should not generate more invoices')
		));

		$generatedOrdersIDs = CustomerOrder::generateRecurringInvoices('2010-01-19');
		$this->assertInvoices($generatedOrdersIDs, array(
			'count' => array(1, '3 days before period 3 starts, should generate invoice'),
			'periods' => array(
				array('2010-01-22', '2010-01-28')
			),
			'rebillsLeft' => array(0)
		));

		$generatedOrdersIDs = CustomerOrder::generateRecurringInvoices('2010-02-02');
		$this->assertInvoices($generatedOrdersIDs, array(
			'count' => array(0, 'period 3 expired'),
		));

	}


	public function testGetNextRebillDate()
	{
		// config
		$config = ActiveRecordModel::getApplication()->getConfig();
		$config->set('RECURRING_BILLING_GENERATE_INVOICE', 0); // generate when period starts!
		$config->save();

		// two products
		$product1 = $this->products[0];
		$price1 = 10.01;
		$period1 = $this->createRecurringProductPeriod($product1, 1, RecurringProductPeriod::TYPE_PERIOD_MONTH, 6); // each month, 6 times

		// one empty order
		$order = CustomerOrder::getNewInstance($this->user);
		$order->save(true);

		// added products
		list($orderedItem1, $recurringItem1) = $this->addRecurringProduct($order, $product1, 1, $period1, 0, $price1);
		$order->save();
		$order->startDate->set('2011-01-01 00:00:00');
		$order->finalize();
		$order->save();

		$date = $order->getNextRebillDate();
		$this->assertEquals('2011-02-01', $date['date_short']);

		$ids = CustomerOrder::generateRecurringInvoices('2011-02-01'); // 1

		$date = $order->getNextRebillDate();
		$this->assertEquals(1, count($ids));
		$this->assertEquals('2011-03-01', $date['date_short']);

		$ids = CustomerOrder::generateRecurringInvoices('2011-03-01'); // 2

		$date = $order->getNextRebillDate();
		$this->assertEquals(1, count($ids));
		$this->assertEquals('2011-04-01', $date['date_short']);

		$ids = CustomerOrder::generateRecurringInvoices('2011-04-01'); // 3

		$date = $order->getNextRebillDate();
		$this->assertEquals(1, count($ids));
		$this->assertEquals('2011-05-01', $date['date_short']);

		$ids = CustomerOrder::generateRecurringInvoices('2011-05-01'); // 4

		$date = $order->getNextRebillDate();
		$this->assertEquals(1, count($ids));
		$this->assertEquals('2011-06-01', $date['date_short']);

		$ids = CustomerOrder::generateRecurringInvoices('2011-06-01'); // 5

		$date = $order->getNextRebillDate();
		$this->assertEquals(1, count($ids));
		$this->assertEquals('2011-07-01', $date['date_short']);

		$ids = CustomerOrder::generateRecurringInvoices('2011-07-01'); // 6
		$date = $order->getNextRebillDate();
		$this->assertEquals(1, count($ids));
		$this->assertEquals(null, $date);

		$ids = CustomerOrder::generateRecurringInvoices('2011-08-01'); // 7
		$date = $order->getNextRebillDate();
		$this->assertEquals(0, count($ids));
		$this->assertEquals(null, $date);

	}

	private function assertInvoices($ids, $assertionRules = array())
	{
		foreach($assertionRules as $key=>$rule)
		{
			if (is_array($rule) == false)
			{
				$assertionRules[$key] = array($rule,'');
			}
		}

		$invoices = array();
		if (count($ids))
		{
			foreach($ids as $id)
			{
				$invoice = CustomerOrder::getInstanceByID($id, true);
				$invoice->loadAll();
				$invoices[] = $invoice;
			}
		}

		foreach($assertionRules as $key=>$rule)
		{
			switch($key)
			{
				case 'count':
					$value = $rule[0];
					$message = $rule[1];
					$this->assertEquals($value, count($ids), $message);
					break;
				case 'totalPrice':
					$value = $rule[0];
					$message = $rule[1];
					$total = 0;
					foreach($invoices as $invoice)
					{
						$total += $invoice->getTotal(true);
					}
					$this->assertEquals($value, $total, $message);
					break;

				case 'orderedItemCount':
					$value = $rule[0];
					$message = $rule[1];
					$total = 0;
					foreach($invoices as $invoice)
					{
						$total += count($invoice->getOrderedItems());
					}
					$this->assertEquals($value, $total, $message);
					break;
					
				case 'periods':
					$periodsInDb = array();
					foreach($invoices as $invoice)
					{
						$start = $invoice->startDate;
						$end = $invoice->endDate;
						$key = sprintf('%s - %s', trim($start) ? date('Y-m-d',strtotime($start)) : 'NULL', trim($end) ? date('Y-m-d',strtotime($end)) : 'NULL' );
						if (array_key_exists($key, $periodsInDb))
						{
							$periodsInDb[$key]++;
						}
						else
						{
							$periodsInDb[$key] = 1;
						}
					}

					foreach($rule as $period)
					{
						$periodKey = sprintf('%s - %s',$period[0], $period[1]);
						$message = count($period) >= 3 ? $period[2] : 'Period "'. $periodKey.'" not found';
						$this->assertTrue(array_key_exists($periodKey, $periodsInDb),$message.'. Not asserted period(s) left: '.$this->_periodsToString($periodsInDb));
						
						$periodsInDb[$periodKey]--;
						if ($periodsInDb[$periodKey] == 0)
						{
							unset($periodsInDb[$periodKey]);
						}
					}

					$this->assertFalse((bool)count($periodsInDb),
						'Required period(s): '.$this->_periodsToString($periodsInDb). ' was not found');
					break;

				case 'rebillsLeft':

					$rebillsLeftInDb = array();
					foreach ($invoices as $invoice)
					{
						$key = '_'.$invoice->rebillsLeft;
						if (array_key_exists($key, $rebillsLeftInDb))
						{
							$rebillsLeftInDb[$key]++;
						}
						else
						{
							$rebillsLeftInDb[$key] = 1;
						}
					}

					foreach ($rule as $rebillsLeft)
					{
						$rebillsLeft = '_'.$rebillsLeft;
						$this->assertTrue(array_key_exists($rebillsLeft, $rebillsLeftInDb), 'rebillsLeft: '.str_replace('_','', $rebillsLeft).' not found. RebillsLeft in database('.count(array_keys($rebillsLeftInDb)).'): '.str_replace('_', '', implode(',', array_keys($rebillsLeftInDb))) );
						
						$rebillsLeftInDb[$rebillsLeft]--;
						if ($rebillsLeftInDb[$rebillsLeft] == 0)
						{
							unset($rebillsLeftInDb[$rebillsLeft]);
						}
					}

					$this->assertFalse((bool)count($rebillsLeftInDb),
						'Required rebillLeft counts: '.$this->_periodsToString($rebillsLeftInDb). ' was not found');
					break;
			}
		}
	}

	private function _periodsToString($periods)
	{
		$string = array();
		foreach ($periods as $period => $times)
		{
			$string[] =  '['.$period.']x'.$times;
		}
		return implode(';', $string);
	}

	private function assertIntervalHasNoInvoicesToGenerate($start, $end)
	{
		for($ts = strtotime($start); $ts <= strtotime($end); $ts = $ts + (60 * 60 * 24))
		{
			$generatedOrdersIDs = CustomerOrder::generateRecurringInvoices($ts);
			$this->assertEquals(0, count($generatedOrdersIDs), 'At date '.date('Y-m-d', $ts).' should not generate invoice, but generated: '.count($generatedOrdersIDs).' invoice(s)');
		}
	}

	private function addRecurringProduct($order, $product, $count, $recurringProductPeriod, $setupPrice=0, $periodPrice=0, $shipment=null)
	{
		$item = $order->addProduct($product, $count, true, $shipment);
		$item->save();
		$recurringItem = RecurringItem::getNewInstance($recurringProductPeriod, $item);
		// pass setup and period prices here because createRecurringProductPeriod() does not create prices in ProductPrice table.
		$recurringItem->setupPrice->set((float)$setupPrice);
		$recurringItem->periodPrice->set((float)$periodPrice);
		$recurringItem->save();
		$product->type->set(Product::TYPE_RECURRING);

		return array($item, $recurringItem);
	}

	private function createRecurringProductPeriod($product, $periodLength=28, $periodType=1, $rebillCount=100)
	{
		$rpp = RecurringProductPeriod::getNewInstance($product);
		$rpp->name->set('Test recurring #'.floor(mt_rand()*1000));
		$rpp->periodLength->set($periodLength);
		$rpp->periodType->set($periodType);
		$rpp->rebillCount->set($rebillCount);
		$rpp->description->set('Test recurring product period');
		$rpp->save();

		return $rpp;
	}

	private function createOrderWithZone(DeliveryZone $zone = null)
	{
		if (is_null($zone))
		{
			$zone = DeliveryZone::getNewInstance();
		}

		$zone->name->set('Latvia');
		$zone->isEnabled->set(true);
		$zone->save();
		$this->newZone = $zone;

		$country = DeliveryZoneCountry::getNewInstance($zone, 'LV');
		$country->save();

		$tax = Tax::getNewInstance('VAT');
		$tax->save();

		$taxRate = TaxRate::getNewInstance($zone, $tax, 20);
		$taxRate->save();

		$service = ShippingService::getNewInstance($zone, 'def', ShippingService::SUBTOTAL_BASED);
		$service->save();
		$this->newService = $service;

		$shippingRate = ShippingRate::getNewInstance($service, 0, 10000000);
		$shippingRate->flatCharge->set(100);
		$shippingRate->save();
		$this->newRate = $shippingRate;

		// user address
		$address = UserAddress::getNewInstance();
		$address->countryID->set('LV');

		$billingAddress = BillingAddress::getNewInstance($this->user, $address);
		$billingAddress->save();

		// set up order
		$this->order->user->set($this->user);
		$this->order->billingAddress->set($address);
		$this->order->shippingAddress->set($address);
		$this->order->save();
	}
}
