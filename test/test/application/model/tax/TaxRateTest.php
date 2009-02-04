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
class TaxRateTest extends UnitTest
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

		$this->deliveryZone = DeliveryZone::getNewInstance();
		$this->deliveryZone->setValueByLang('name', 'en', 'test zone');
		$this->deliveryZone->save();

		$this->tax = Tax::getNewInstance('test type');
		$this->tax->save();
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

	public function testDefaultZoneVAT()
	{
		ActiveRecord::executeUpdate('DELETE FROM TaxRate');

		$taxRate = TaxRate::getNewInstance(DeliveryZone::getDefaultZoneInstance(), $this->tax, 10);
		$taxRate->save();

		$currency = ActiveRecord::getInstanceByIdIfExists('Currency', 'USD');
		$currency->isEnabled->set(true);
		$currency->decimalCount->set(2);
		$currency->save();

		$product = Product::getNewInstance(Category::getRootNode());
		$product->setPrice('USD', 100);
		$product->isEnabled->set(true);
		$product->save();

		$user = User::getNewInstance('vat.test@tester.com');
		$user->save();

		$order = CustomerOrder::getNewInstance($user);
		$order->addProduct($product, 1, true);
		$order->currency->set($currency);
		$order->save();

		$this->assertEqual($order->getTotal($currency), 100);
		$order->finalize($currency);

		$this->assertDefaultZoneOrder($order, $currency);

		ActiveRecord::clearPool();
		$reloaded = CustomerOrder::getInstanceById($order->getID(), true);

		$this->assertDefaultZoneOrder($reloaded, $currency);
	}

	private function assertDefaultZoneOrder(CustomerOrder $order, Currency $currency)
	{
		$this->assertEqual($order->getTotal($currency), 100);

		$shipment = $order->getShipments()->get(0);
		$this->assertEqual($shipment->getSubTotal($currency, true), 100);
		$this->assertEqual(round($shipment->getSubTotal($currency, false), 2), 90.91);

		$arr = $order->toArray();
		$this->assertEqual($arr['cartItems'][0]['displayPrice'], 100);
		$this->assertEqual($arr['cartItems'][0]['displaySubTotal'], 100);
		$this->assertEqual(round($arr['cartItems'][0]['itemSubTotal'], 2), 90.91);
	}

	public function testDefaultZoneVATWithAnotherZone()
	{
		ActiveRecord::executeUpdate('DELETE FROM TaxRate');

		TaxRate::getNewInstance(DeliveryZone::getDefaultZoneInstance(), $this->tax, 10)->save();
		TaxRate::getNewInstance($this->deliveryZone, $this->tax, 10)->save();

		$currency = ActiveRecord::getInstanceByIdIfExists('Currency', 'USD');
		$currency->isEnabled->set(true);
		$currency->decimalCount->set(2);
		$currency->save();

		$product = Product::getNewInstance(Category::getRootNode());
		$product->setPrice('USD', 100);
		$product->isEnabled->set(true);
		$product->save();

		$user = User::getNewInstance('vat.test@tester.com');
		$user->save();

		$address = UserAddress::getNewInstance();
		$address->countryID->set('US');
		$address->save();

		DeliveryZoneCountry::getNewInstance($this->deliveryZone, 'US')->save();

		$order = CustomerOrder::getNewInstance($user);
		$order->addProduct($product, 1, true);
		$order->currency->set($currency);
		$order->shippingAddress->set($address);
		$order->save();

		$this->assertEqual($order->getTotal($currency), 100);
		$order->finalize($currency);

		$this->assertDefaultZoneOrder($order, $currency);

		ActiveRecord::clearPool();
		$reloaded = CustomerOrder::getInstanceById($order->getID(), true);

		$this->assertDefaultZoneOrder($reloaded, $currency);
	}
}
?>