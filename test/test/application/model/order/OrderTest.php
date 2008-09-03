<?php

if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.category.*");
ClassLoader::import("application.model.product.*");
ClassLoader::import("application.model.discount.*");
ClassLoader::import("application.model.order.*");
ClassLoader::import("application.model.user.User");
ClassLoader::import("application.model.user.*");
ClassLoader::import("application.model.Currency");
ClassLoader::import("library.payment.*");

/**
 *	Test Order model
 *
 *  @author Integry Systems
 *  @package test.model.order
 */
class OrderTest extends UnitTest
{
	private $order;

	private $products = array();

	private $usd;

	private $user;

	public function setUp()
	{
		parent::setUp();

		ActiveRecordModel::beginTransaction();

		ActiveRecord::executeUpdate('DELETE FROM TaxRate');
		ActiveRecord::executeUpdate('DELETE FROM Currency');
		ActiveRecordModel::executeUpdate('DELETE FROM DeliveryZone');

		// set up currency
		if (ActiveRecord::objectExists('Currency', 'USD'))
		{
			$this->usd = Currency::getInstanceByID('USD', Currency::LOAD_DATA);
		}
		else
		{
			$this->usd = Currency::getNewInstance('USD');
			$this->usd->setAsDefault();
			$this->usd->save();
		}

		// initialize order
		ActiveRecordModel::executeUpdate('DELETE FROM User WHERE email="test@test.com"');
		$user = User::getNewInstance('test@test.com');
		$user->save();
		$this->user = $user;

		$address = UserAddress::getNewInstance();
		$address->countryID->set('US');
		$state = State::getInstanceById(1, State::LOAD_DATA);
		$address->state->set(State::getInstanceById(1));
		$address->postalCode->set(90210);
		$address->save();
		$billing = BillingAddress::getNewInstance($user, $address);
		$billing->save();

		$address = clone $address;
		$shipping = ShippingAddress::getNewInstance($user, $address);
		$shipping->save();

		$user->defaultBillingAddress->set($billing);
		$user->defaultShippingAddress->set($shipping);
		$user->save();

		$this->order = CustomerOrder::getNewInstance($user);
		$this->order->shippingAddress->set($shipping->userAddress->get());
		$this->order->billingAddress->set($billing->userAddress->get());

		// set up products
		$product = Product::getNewInstance(Category::getInstanceById(Category::ROOT_ID), 'test1');
		$product->save();
		$product->setPrice('USD', 100);
		$product->stockCount->set(20);
		$product->isEnabled->set(1);
		$product->save();
		$this->products[] = $product;

		$product = Product::getNewInstance(Category::getInstanceById(Category::ROOT_ID), 'test2');
		$product->save();
		$product->setPrice('USD', 200);
		$product->stockCount->set(20);
		$product->isEnabled->set(1);
		$product->save();
		$this->products[] = $product;

		$product = Product::getNewInstance(Category::getInstanceById(Category::ROOT_ID), 'test3');
		$product->save();
		$product->setPrice('USD', 400);
		$product->isSeparateShipment->set(true);
		$product->stockCount->set(20);
		$product->isEnabled->set(1);
		$product->save();
		$this->products[] = $product;
	}

	public function getUsedSchemas()
	{
		return array(
			'CustomerOrder',
			'OrderedItem',
			'Shipment',
		);
	}

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

		$this->order->finalize($this->usd);
		$total = $this->order->getTotal($this->usd);

		// the sum of all shipments amounts should be equal to the order amount
		$sum = 0;
		foreach ($this->order->getShipments() as $shipment)
		{
			$sum += $shipment->amount->get();
		}

		$this->assertEqual($sum, $this->order->totalAmount->get());

		ActiveRecord::clearPool();

		// reload the whole order data - the calculated total should still match
		$order = CustomerOrder::getInstanceById($this->order->getID(), true);
		$order->loadAll();
		$this->assertEqual($total, $order->getTotal($this->usd));

		// change price for one product...
		foreach ($order->getShoppingCartItems() as $item)
		{
			$product = $item->product->get();
			$product->setPrice('USD', $product->getPrice('USD') + 10);
//			$order->removeProduct($product);
//			var_dump(count($order->getShoppingCartItems()));
			$order->save();
//			var_dump(count($order->getShoppingCartItems()));
//			$order->save();
//			var_dump(count($order->getShoppingCartItems()));
//			var_dump($order->totalAmount->get() . '!');
//			var_dump('test');
//			$order->addProduct($product, 1);
			$order->save();
//			var_dump('test');
//			var_dump(count($order->getShoppingCartItems()));
//			var_dump($order->getShoppingCartItems());
			break;
		}

//		var_dump($order->getTotal($this->usd));

		// ...so the new total calculated total would be different
		// $this->assertNotEqual($total, $order->getTotal($this->usd));

		// however the "closed" price should still be the same as this order is already finalized
		$this->assertEqual($total, $order->totalAmount->get());
	}

	function testPayment()
	{
		$this->order->addProduct($this->products[0], 1);
		$this->order->addProduct($this->products[1], 1);
		$this->order->save();

		$this->order->finalize($this->usd);

		$result = new TransactionResult();
		$result->amount->set($this->order->totalAmount->get());
		$result->currency->set($this->order->currency->get()->getID());
		$result->gatewayTransactionID->set('TESTTRANSACTION');
		$result->setTransactionType(TransactionResult::TYPE_SALE);

		$transaction = Transaction::getNewInstance($this->order, $result);
		$transaction->save();

		$this->assertEqual($this->order->totalAmount->get(), $this->order->capturedAmount->get());
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
		$order->finalize($this->usd);

		$second->save();

		// empty orders (without items) should not be saved
		$this->assertNull($second->getID());

		ActiveRecord::clearPool();

		$order = CustomerOrder::getInstanceById($order->getID());
		$order->loadAll();
		$this->assertEqual(count($order->getOrderedItems()), 2);
	}

	function testUpdateCounts()
	{
		$product = $this->products[0];
		$order = CustomerOrder::getNewInstance($this->user);

		// allow fractional units
		$product->isFractionalUnit->set(true);
		$order->addProduct($product, 1.5);
		$items = $order->getItemsByProduct($product);
		$this->assertEqual($items[0]->count->get(), 1.5);

		// disable fractional units
		$product->isFractionalUnit->set(false);
		$order->updateCount($items[0], 1.2);
		$this->assertEqual($items[0]->count->get(), 1);

		$order->removeProduct($product);
		$order->addProduct($product, 3.3);
		$items = $order->getItemsByProduct($product);
		$this->assertEqual($items[0]->count->get(), 3);
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

		$order->finalize($this->usd);
		$this->assertEqual($order->getSubTotal($this->usd), $price);

		ActiveRecord::clearPool();

		$loadedOrder = CustomerOrder::getInstanceById($order->getID());
		$loadedOrder->loadAll();
		$this->assertEqual($loadedOrder->getSubTotal($this->usd), $price);
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
		$order->finalize($this->usd);

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
		$this->assertEqual($shipment->getTotal(), $itemPriceWithTax);

		$rates = $zone->getShippingRates($shipment);
		$shipment->setAvailableRates($rates);
		$shipment->setRateId($rates->get(0)->getServiceID());
		$shipment->save();

		$this->assertEqual($this->order->getSubTotalBeforeTax($this->usd), $total - $tax);
		$this->assertEqual($this->order->getTotal($this->usd), $total);
		$this->order->save();

		ActiveRecord::clearPool();
		$order = CustomerOrder::getInstanceByID($this->order->getID(), true);
		$order->loadAll();

		$this->assertEqual($order->getShipments()->get(0)->getTaxAmount($this->usd), $tax);

		$order->finalize($this->usd);

		$this->assertEqual($order->getShipments()->get(0)->getTaxAmount($this->usd), $tax);

		$this->assertEqual($order->getTotal($this->usd), $total);

		ActiveRecord::clearPool();
		$order = CustomerOrder::getInstanceByID($this->order->getID());
		$order->loadAll();
		$this->assertEqual($order->getTotal($this->usd), $total);
	}

	public function testInventory()
	{
		$this->config->set('INVENTORY_TRACKING', 'ENABLE_AND_HIDE');

		$product = $this->products[0];
		$product->stockCount->set(2);
		$product->save();

		$order = CustomerOrder::getNewInstance($this->user);
		$order->addProduct($product, 1);
		$order->save();
		$order->finalize($this->usd);

		$product->reload();
		$this->assertEqual($product->reservedCount->get(), 1);

		// mark order as shipped - the stock is gone
		$order->setStatus(CustomerOrder::STATUS_SHIPPED);
		foreach ($order->getShipments() as $shipment)
		{
			$this->assertEqual($shipment->status->get(), Shipment::STATUS_SHIPPED);
		}

		$this->assertEqual($product->stockCount->get(), 1);
	}

	public function testOrderingABundle()
	{
		$container = Product::getNewInstance(Category::getRootNode());
		$container->isEnabled->set(true);
		$container->type->set(Product::TYPE_BUNDLE);
		$container->setPrice($this->usd, 100);
		$container->save();

		foreach ($this->products as $product)
		{
			ProductBundle::getNewInstance($container, $product)->save();
		}

		$this->assertTrue($container->isAvailable());

		foreach ($this->products as $product)
		{
			$product->stockCount->set(2);
			$product->save();
		}

		$this->config->set('INVENTORY_TRACKING', 'ENABLE_AND_HIDE');

		$order = CustomerOrder::getNewInstance($this->user);
		$order->addProduct($container, 1);
		$order->save();
		$order->finalize($this->usd);

		$this->assertEqual($order->getTotal($this->usd), 100);

		$containerItem = array_shift($order->getItemsByProduct($container));
		$this->assertSame($containerItem->product->get(), $container);

		$subItems = $containerItem->getSubItems();
		$this->assertEqual($subItems->size(), count($this->products));

		// the sub-items should never show up in the order product list
		ActiveRecord::clearPool();
		$reloaded = CustomerOrder::getInstanceByID($order->getID());
		$reloaded->loadItems();
		$this->assertEqual(count($reloaded->getOrderedItems()), 1);

		// check inventory
		foreach ($this->products as $product)
		{
			$this->assertEqual($product->reservedCount->get(), 1);
			$this->assertEqual($product->stockCount->get(), 2);
		}

		// mark order as shipped - the stock is gone
		$this->assertNotEquals($order->status->get(), CustomerOrder::STATUS_SHIPPED);
		$order->setStatus(CustomerOrder::STATUS_SHIPPED);
		foreach ($order->getShipments() as $shipment)
		{
			$this->assertEqual($shipment->status->get(), Shipment::STATUS_SHIPPED);
		}

		foreach ($this->products as $product)
		{
			$product->reload();
			$this->assertEqual($product->reservedCount->get(), 0);
			$this->assertEqual($product->stockCount->get(), 1);
		}
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
		$order->finalize($this->usd);

		$this->assertEqual($order->getShipments()->size(), 1);
		$this->assertFalse($order->getShipments()->get(0)->isShippable());
	}

	public function testFixedDiscountWithoutTaxAndShipping()
	{
		$this->order->addProduct($this->products[0]);
		$this->order->getShipments();
		$this->order->save();

		// before finalizing
		$total = $this->order->getTotal($this->usd);

		$discount = OrderDiscount::getNewInstance($this->order);
		$discount->amount->set(10);
		$discount->save();

		$this->assertEquals($this->order->getTotal($this->usd), $total - 10);
		$this->order->save();

		// finalized
		$this->order->finalize($this->usd);
		$this->assertEquals($this->order->getTotal($this->usd), $total - 10);

		// reload order
		ActiveRecordModel::clearPool();
		$order = CustomerOrder::getInstanceByID($this->order->getID());
		$order->loadAll();
		$this->assertEquals($order->getTotal($this->usd), $total - 10);

		// modify reloaded order
		$newTotal = $this->products[0]->getPrice($this->usd) + $this->products[1]->getPrice($this->usd);
		$order->addProduct($this->products[1]);
		$order->save();
		$this->assertEquals($order->getTotal($this->usd), $newTotal - 10);
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

		$initTotal = $this->order->getTotal($this->usd);
		$initTax = $this->order->getTaxAmount();

		$discount = OrderDiscount::getNewInstance($this->order);
		$discount->amount->set(10);
		$discount->save();

		$this->assertEquals((int)$this->order->getTotal($this->usd), (int)($initTotal - 10));
		$tax = $this->order->getTaxAmount();

		$expectedTax = (110 / 6) + 20;
		$this->assertEquals(round($expectedTax, 2), round($tax, 2));
	}

	public function testSimpleItemDiscount()
	{
		$condition = DiscountCondition::getNewInstance();
		$condition->isEnabled->set(true);
		$condition->save();

		$record = DiscountConditionRecord::getNewInstance($condition, $this->products[0]);
		$record->save();
		$condition->loadAll();

		$action = DiscountAction::getNewInstance($condition);
		$action->isEnabled->set(true);
		$action->type->set(DiscountAction::TYPE_ITEM_DISCOUNT);
		$action->amount->set(10);
		$action->amountMeasure->set(DiscountAction::MEASURE_PERCENT);
		$action->save();

		$this->order->addProduct($this->products[0]);
		$this->order->addProduct($this->products[1]);
		$this->order->save();

		// order wide 10% discount on all items
		$originalTotal = $this->products[0]->getPrice($this->usd) + $this->products[1]->getPrice($this->usd);
		$this->assertEquals($this->order->getTotal($this->usd), $originalTotal * 0.9);

		// discount applied on the same items that matched the rules
		$action->actionCondition->set($condition);
		$expectedTotal = ($this->products[0]->getPrice($this->usd) * 0.9) + $this->products[1]->getPrice($this->usd);
		$this->assertEquals($this->order->getTotal($this->usd), $expectedTotal);

		// apply discount to the second item as well
		$record = DiscountConditionRecord::getNewInstance($condition, $this->products[1]);
		$record->save();
		$condition->loadAll();
		$this->assertEquals($this->order->getTotal($this->usd), $originalTotal * 0.9);
	}

	public function testApplyingTwoDiscountsToOneItemAndOneToOther()
	{
		for ($k = 1; $k <= 2; $k++)
		{
			$cond[$k] = DiscountCondition::getNewInstance();
			$cond[$k]->isEnabled->set(true);
			$cond[$k]->save();

			$action[$k] = DiscountAction::getNewInstance($cond[$k]);
			$action[$k]->isEnabled->set(true);
			$action[$k]->type->set(DiscountAction::TYPE_ITEM_DISCOUNT);
			$action[$k]->amount->set(10 * $k);
			$action[$k]->amountMeasure->set(DiscountAction::MEASURE_PERCENT);
			$action[$k]->save();
		}

		$this->order->addProduct($this->products[0]);
		$this->order->addProduct($this->products[1]);
		$this->order->save();

		// order wide 28% discount (cumulative 10% and 20% discount) on all items
		$originalTotal = $this->products[0]->getPrice($this->usd) + $this->products[1]->getPrice($this->usd);
		$this->assertEquals($this->order->getTotal($this->usd), $originalTotal * 0.72);

		// apply 20% discount to both items, and 10% discount to first item only
		$record = DiscountConditionRecord::getNewInstance($cond[1], $this->products[0]);
		$record->save();
		$cond[1]->loadAll();
		$this->assertFalse($cond[1]->isProductMatching($this->products[1]));

		$action[1]->actionCondition->set($cond[1]);
		$action[1]->save();

		$expectedTotal = ($this->products[0]->getPrice($this->usd) * 0.9 * 0.8) + ($this->products[1]->getPrice($this->usd) * 0.8);
		$this->assertEquals($this->order->getTotal($this->usd), $expectedTotal);
	}

	public function testDiscountPriority()
	{
		for ($k = 1; $k <= 2; $k++)
		{
			$cond[$k] = DiscountCondition::getNewInstance();
			$cond[$k]->isEnabled->set(true);
			$cond[$k]->save();

			$action[$k] = DiscountAction::getNewInstance($cond[$k]);
			$action[$k]->isEnabled->set(true);
			$action[$k]->type->set(DiscountAction::TYPE_ITEM_DISCOUNT);
			$action[$k]->amount->set(10 * $k);
			$action[$k]->amountMeasure->set(1 - ($k - 1)); // 0 - percent, 1 - amount
			$action[$k]->save();
		}

		$price0 = $this->products[0]->getPrice($this->usd);
		$price1 = $this->products[1]->getPrice($this->usd);
		$total = $price0 + $price1;

		$this->order->addProduct($this->products[0]);
		$this->order->addProduct($this->products[1]);
		$this->order->save();

		$expectedTotal = (($price0 - 10) * 0.8) + (($price1 - 10) * 0.8);
		$this->assertEquals($this->order->getTotal($this->usd), $expectedTotal);

		// switch priorities
		$action[1]->position->set(1);
		$action[1]->save();
		$action[2]->position->set(0);
		$action[2]->save();

		$this->order->getDiscountActions(true);
		$expectedTotal = (($price0 * 0.8) - 10) + (($price1 * 0.8) - 10);
		$this->assertEquals($this->order->getTotal($this->usd), $expectedTotal);
	}

	public function testDisabledDiscountConditions()
	{
		for ($k = 1; $k <= 2; $k++)
		{
			$cond[$k] = DiscountCondition::getNewInstance();
			$cond[$k]->isEnabled->set($k == 1);
			$cond[$k]->save();

			$action[$k] = DiscountAction::getNewInstance($cond[$k]);
			$action[$k]->isEnabled->set(true);
			$action[$k]->type->set(DiscountAction::TYPE_ITEM_DISCOUNT);
			$action[$k]->amount->set(10 * $k);
			$action[$k]->amountMeasure->set(1 - ($k - 1)); // 0 - percent, 1 - amount
			$action[$k]->save();
		}

		$price0 = $this->products[0]->getPrice($this->usd);
		$price1 = $this->products[1]->getPrice($this->usd);
		$total = $price0 + $price1;

		$this->order->addProduct($this->products[0]);
		$this->order->addProduct($this->products[1]);
		$this->order->save();

		$this->assertEquals($this->order->getTotal($this->usd), $total - 20);

		// add a second action
		$act = DiscountAction::getNewInstance($cond[1]);
		$act->isEnabled->set(true);
		$act->type->set(DiscountAction::TYPE_ITEM_DISCOUNT);
		$act->amount->set(30);
		$act->amountMeasure->set(DiscountAction::MEASURE_AMOUNT); // 0 - percent, 1 - amount
		$act->save();
		$cond[1]->loadAll();

		$this->order->getDiscountActions(true);
		$this->assertEquals($this->order->getTotal($this->usd), $total - 20 - 60);

		// and disable it
		$act->isEnabled->set(false);
		$act->save();

		$this->order->getDiscountActions(true);
		$this->assertEquals($this->order->getTotal($this->usd), $total - 20);
	}

	private function createOrderWithZone(DeliveryZone $zone = null)
	{
		if (is_null($zone))
		{
			$zone = DeliveryZone::getNewInstance();
		}

		$zone->name->set('Latvia');
		$zone->save();

		$country = DeliveryZoneCountry::getNewInstance($zone, 'LV');
		$country->save();

		$tax = Tax::getNewInstance('VAT');
		$tax->save();

		$taxRate = TaxRate::getNewInstance($zone, $tax, 20);
		$taxRate->save();

		$service = ShippingService::getNewInstance($zone, 'def', ShippingService::SUBTOTAL_BASED);
		$service->save();

		$shippingRate = ShippingRate::getNewInstance($service, 0, 10000000);
		$shippingRate->flatCharge->set(100);
		$shippingRate->save();

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