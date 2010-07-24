<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.user.*");
ClassLoader::import("application.model.order.CustomerOrder");
ClassLoader::import("application.model.delivery.*");
ClassLoader::import("application.model.tax.Tax");
ClassLoader::import("application.model.tax.TaxRate");
ClassLoader::import("application.model.product.Product");

/**
 * @author Integry Systems
 * @package test.model.tax
 */
class TaxRateTest extends LiveCartTest
{
	/**
	 * Delivery zone
	 *
	 * @var DeliveryZone
	 */
	private $deliveryZone = null;

	public function __construct()
	{
		parent::__construct('Tax rate tests');
	}

	public function getUsedSchemas()
	{
		return array(
			'TaxRate',
			'Tax',
			'DeliveryZone',
			'CustomerOrder',
			'Shipment',
			'OrderedItem',
			'ShipmentTax',
		);
	}

	public function setUp()
	{
		parent::setUp();

		ActiveRecord::executeUpdate('DELETE FROM Tax');
		ActiveRecord::executeUpdate('DELETE FROM DeliveryZone');
		ActiveRecord::executeUpdate('DELETE FROM Currency');
		ActiveRecord::executeUpdate('DELETE FROM ShippingService');

		$this->deliveryZone = DeliveryZone::getNewInstance();
		$this->deliveryZone->setValueByLang('name', 'en', 'test zone');
		$this->deliveryZone->isEnabled->set(true);
		$this->deliveryZone->save();
		DeliveryZoneCountry::getNewInstance($this->deliveryZone, 'US')->save();

		$this->tax = Tax::getNewInstance('test type');
		$this->tax->save();

		$this->currency = ActiveRecord::getInstanceByIdIfExists('Currency', 'USD');
		$this->currency->isEnabled->set(true);
		$this->currency->decimalCount->set(2);
		$this->currency->save();

		$this->product = Product::getNewInstance(Category::getRootNode());
		$this->product->setPrice('USD', 100);
		$this->product->isEnabled->set(true);
		$this->product->save();

		$this->user = User::getNewInstance('vat.test2@tester.com');
		$this->user->save();

		$this->address = UserAddress::getNewInstance();
		$this->address->countryID->set('US');
		$this->address->save();
	}

	public function testCreateNewTaxRate()
	{
		$taxRate = TaxRate::getNewInstance($this->deliveryZone, $this->tax, 15);
		$taxRate->save();

		$taxRate->reload();

		$this->assertEqual($taxRate->rate->get(), 15);
		$this->assertTrue($taxRate->deliveryZone->get() === $this->deliveryZone);
		$this->assertTrue($taxRate->tax->get() === $this->tax);
	}

	public function testSimpleTax()
	{
		TaxRate::getNewInstance($this->deliveryZone, $this->tax, 10)->save();
		DeliveryZoneCountry::getNewInstance($this->deliveryZone, 'US')->save();

		$order = CustomerOrder::getNewInstance($this->user);
		$order->addProduct($this->product, 1, true);
		$order->currency->set($this->currency);
		$order->shippingAddress->set($this->address);
		$order->save();

		$this->assertSame($order->getDeliveryZone(), $this->deliveryZone);
		$this->assertEqual($order->getTotal(), 110);
		$order->finalize();

		ActiveRecord::clearPool();
		$reloaded = CustomerOrder::getInstanceById($order->getID(), true);
		$this->assertEqual($reloaded->getTotal(), 110);
	}

	public function testShipmentTax()
	{
		TaxRate::getNewInstance(DeliveryZone::getDefaultZoneInstance(), $this->tax, 10)->save();
		TaxRate::getNewInstance($this->deliveryZone, $this->tax, 10)->save();
		DeliveryZoneCountry::getNewInstance($this->deliveryZone, 'US')->save();

		$order = CustomerOrder::getNewInstance($this->user);
		$order->addProduct($this->product, 1, true);
		$order->currency->set($this->currency);
		$order->shippingAddress->set($this->address);
		$order->save();

		$this->assertSame($order->getDeliveryZone(), $this->deliveryZone);
		$this->assertEqual($order->getTotal(), 100);
		$order->finalize();

		ActiveRecord::clearPool();
		$reloaded = CustomerOrder::getInstanceById($order->getID(), true);
		$this->assertTrue($reloaded->getShipments()->get(0)->isExistingRecord());
		$this->assertEqual($reloaded->getShipments()->get(0)->getRelatedRecordSet('ShipmentTax')->size(), 1);
	}

	public function testDefaultZoneVAT()
	{

		$taxRate = TaxRate::getNewInstance(DeliveryZone::getDefaultZoneInstance(), $this->tax, 10);
		$taxRate->save();

		$order = CustomerOrder::getNewInstance($this->user);
		$order->addProduct($this->product, 1, true);
		$order->currency->set($this->currency);
		$order->save();

		$this->assertEqual($order->getTotal(), 100);
		$order->finalize();

		$this->assertDefaultZoneOrder($order, $this->currency);

		ActiveRecord::clearPool();
		ActiveRecord::clearArrayData();
		$reloaded = CustomerOrder::getInstanceById($order->getID(), true);
		/* debug
	    $reloaded->getShipments();
	    $arr = $reloaded->toArray();
		foreach($arr['cartItems'][0] as $k=>$v)
			if(!is_array($v))
				echo $k.' : '.$v."\n";
		*/
		$this->assertDefaultZoneOrder($reloaded, $this->currency);
	}

	private function assertDefaultZoneOrder(CustomerOrder $order, Currency $currency)
	{
		$this->assertEquals(100, $order->getTotal());
		$shipment = $order->getShipments()->get(0);

		$shipmentArray = $shipment->toArray();

		$this->assertEquals(9.09, round($shipmentArray['taxAmount'],2), 'shipment array, taxAmount');
		$this->assertEquals(90.91, round($shipmentArray['amount'],2), 'shipment array, amount');
		$this->assertEquals(100.0, round($shipmentArray['amount'] + $shipmentArray['taxAmount'],2), 'shipment array, amount + taxAmount');
//if($t);
//print_r($shipmentArray);

		$this->assertEquals(100, round($shipment->getSubTotal(true), 2), '$shipment->getSubTotal(<with taxes>)');
		$this->assertEquals(90.91, round($shipment->getSubTotal(false), 2), '$shipment->getSubTotal(<without taxes>)');

		$arr = $order->toArray();
		$this->assertEquals(100, $arr['cartItems'][0]['displayPrice']);
		$this->assertEquals(100, $arr['cartItems'][0]['displaySubTotal']);
//		$this->assertEqual(round($arr['cartItems'][0]['itemSubTotal'], 2), 90.91);
	}

	public function testDefaultZoneVATWithAnotherZone()
	{
		TaxRate::getNewInstance(DeliveryZone::getDefaultZoneInstance(), $this->tax, 10)->save();
		TaxRate::getNewInstance($this->deliveryZone, $this->tax, 10)->save();

		DeliveryZoneCountry::getNewInstance($this->deliveryZone, 'US')->save();

		$order = CustomerOrder::getNewInstance($this->user);
		$order->addProduct($this->product, 1, true);
		$order->currency->set($this->currency);
		$order->shippingAddress->set($this->address);
		$order->save();

		$this->assertEqual($order->getTotal($this->currency), 100);
		$order->finalize();

		$this->assertDefaultZoneOrder($order, $this->currency);

		ActiveRecord::clearPool();
		$reloaded = CustomerOrder::getInstanceById($order->getID(), true);

		$this->assertDefaultZoneOrder($reloaded, $this->currency);
	}

	public function testTaxClasses()
	{
		// default tax level
		TaxRate::getNewInstance($this->deliveryZone, $this->tax, 20)->save();

		// diferent tax rate for books
		$books = TaxClass::getNewInstance('Books');
		$books->save();
		$booksRate = TaxRate::getNewInstance($this->deliveryZone, $this->tax, 10);
		$booksRate->taxClass->set($books);
		$booksRate->save();

		// price = 100
		$cd = $this->product;

		$book = Product::getNewInstance(Category::getRootNode());
		$book->setPrice('USD', 50);
		$book->isEnabled->set(true);
		$book->taxClass->set($books);
		$book->save();

		// shipping tax class
		$shpClass = TaxClass::getNewInstance('Shipping');
		$shpClass->save();
		$shippingTaxRate = TaxRate::getNewInstance($this->deliveryZone, $this->tax, 17);
		$shippingTaxRate->taxClass->set($shpClass);
		$shippingTaxRate->save();

		$this->getApplication()->getConfig()->set('DELIVERY_TAX_CLASS', $shpClass->getID());

		$order = CustomerOrder::getNewInstance($this->user);
		$order->addProduct($cd, 1, true);
		$order->addProduct($book, 1, true);
		$order->currency->set($this->currency);
		$order->shippingAddress->set($this->address);
		$order->save();

		$this->assertEqual($order->getDeliveryZone()->getID(), $this->deliveryZone->getID());

		//$order->finalize();
		$this->assertEqual($order->getTotal(true), 120 + 55);
		$this->assertEqual($order->getTaxAmount(), 25);

		$service = ShippingService::getNewInstance($this->deliveryZone, 'def', ShippingService::SUBTOTAL_BASED);
		$service->save();

		$shippingRate = ShippingRate::getNewInstance($service, 0, 10000000);
		$shippingRate->flatCharge->set(100);
		$shippingRate->save();

		$shipment = $order->getShipments()->get(0);
		$rates = $order->getDeliveryZone()->getShippingRates($shipment);
		$shipment->setAvailableRates($rates);
		$shipment->setRateId($rates->get(0)->getServiceID());
		$shipment->save();

		$this->assertEqual($order->getTaxAmount(), 42);
		$this->assertEqual($order->getTotal(true), 120 + 55 + 117);
	}

	public function testTaxClassesWithDefaultZone()
	{
		$order = CustomerOrder::getNewInstance($this->user);

		$zone = $order->getDeliveryZone();
		$this->assertTrue($zone->isDefault());

		// default tax level
		TaxRate::getNewInstance($zone, $this->tax, 20)->save();

		// diferent tax rate for books
		$books = TaxClass::getNewInstance('Books');
		$books->save();
		$booksRate = TaxRate::getNewInstance($zone, $this->tax, 10);
		$booksRate->taxClass->set($books);
		$booksRate->save();

		// price = 100
		$cd = $this->product;

		$book = Product::getNewInstance(Category::getRootNode());
		$book->setPrice('USD', 50);
		$book->isEnabled->set(true);
		$book->taxClass->set($books);
		$book->save();

		$order->addProduct($cd, 1, true);
		$order->addProduct($book, 1, true);
		$order->currency->set($this->currency);
		$order->save();

		$this->assertEqual($order->getTaxAmount(), 21.21);
		$this->assertEqual($order->getTotal(true), 150);
	}

	public function testTaxClassesWithDefaultZoneAndMultipleTaxes()
	{
		$order = CustomerOrder::getNewInstance($this->user);

		$zone = $order->getDeliveryZone();
		$this->assertTrue($zone->isDefault());

		// default tax level
		TaxRate::getNewInstance($zone, $this->tax, 10)->save();

		$newTax = Tax::getNewInstance('test');
		$newTax->save();

		// diferent tax rate for books
		$books = TaxClass::getNewInstance('Books');
		$books->save();

		$booksRate = TaxRate::getNewInstance($zone, $this->tax, 5);
		$booksRate->taxClass->set($books);
		$booksRate->save();

		$booksRate = TaxRate::getNewInstance($zone, $newTax, 20);
		$booksRate->taxClass->set($books);
		$booksRate->save();

		// price = 100
		$cd = $this->product;

		$book = Product::getNewInstance(Category::getRootNode());
		$book->setPrice('USD', 50);
		$book->isEnabled->set(true);
		$book->taxClass->set($books);
		$book->save();

		$order->addProduct($cd, 1, true);
		$order->addProduct($book, 1, true);
		$order->currency->set($this->currency);
		$order->save();

		$this->assertEqual($order->getTaxAmount(), 19.41);
		$this->assertEqual($order->getTotal(true), 150);

		$service = ShippingService::getNewInstance($order->getDeliveryZone(), 'def', ShippingService::SUBTOTAL_BASED);
		$service->save();

		$shippingRate = ShippingRate::getNewInstance($service, 0, 10000000);
		$shippingRate->flatCharge->set(100);
		$shippingRate->save();

		$shipment = $order->getShipments()->get(0);
		$rates = $order->getDeliveryZone()->getShippingRates($shipment);

		$shipment->setAvailableRates($rates);
		$shipment->setRateId($rates->get(0)->getServiceID());
		$shipment->save();

		$this->assertEqual($order->getTotal(true), 250);
		$this->assertEqual((string)$order->getTaxAmount(), (string)28.50);
	}
}
?>