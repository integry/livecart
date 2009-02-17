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

		$this->deliveryZone = DeliveryZone::getNewInstance();
		$this->deliveryZone->setValueByLang('name', 'en', 'test zone');
		$this->deliveryZone->isEnabled->set(true);
		$this->deliveryZone->save();

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

		$this->user = User::getNewInstance('vat.test@tester.com');
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
		$reloaded = CustomerOrder::getInstanceById($order->getID(), true);

		$this->assertDefaultZoneOrder($reloaded, $this->currency);
	}

	private function assertDefaultZoneOrder(CustomerOrder $order, Currency $currency)
	{
		$this->assertEqual($order->getTotal(), 100);

		$shipment = $order->getShipments()->get(0);
		$this->assertEqual($shipment->getSubTotal(true), 100);
		$this->assertEqual(round($shipment->getSubTotal(false), 2), 90.91);

		$arr = $order->toArray();
		$this->assertEqual($arr['cartItems'][0]['displayPrice'], 100);
		$this->assertEqual($arr['cartItems'][0]['displaySubTotal'], 100);
		$this->assertEqual(round($arr['cartItems'][0]['itemSubTotal'], 2), 90.91);
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
		$order->finalize($this->currency);

		$this->assertDefaultZoneOrder($order, $this->currency);

		ActiveRecord::clearPool();
		$reloaded = CustomerOrder::getInstanceById($order->getID(), true);

		$this->assertDefaultZoneOrder($reloaded, $this->currency);
	}
}
?>