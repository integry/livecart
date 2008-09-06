<?php

if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.category.*");
ClassLoader::import("application.model.product.*");
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

	public function __construct()
	{
		parent::__construct('Test order logic');
	}

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

	function test_SuiteTearDown()
	{
		ActiveRecordModel::rollback();
	}
}

?>
