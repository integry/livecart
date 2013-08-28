<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';


/**
 *
 * @package test.model.delivery
 * @author Integry Systems
 */
class ShippingServiceTest extends LiveCartTest
{
	/**
	 * Delivery zone
	 *
	 * @var DeliveryZone
	 */
	private $deliveryZone = null;

	public function __construct()
	{
		parent::__construct('shiping service tests');
	}

	public function getUsedSchemas()
	{
		return array(
			'ShippingService',
			'ShippingRate',
			'DeliveryZone'
		);
	}

	public function setUp()
	{
		parent::setUp();

		$this->deliveryZone = DeliveryZone::getNewInstance();
		$this->deliveryZone->name->set('test zone');
		$this->deliveryZone->save();
	}

	public function testCreateNewService()
	{
		$service = ShippingService::getNewInstance($this->deliveryZone, 'Test service', ShippingService::SUBTOTAL_BASED);
		$service->position->set(1);
		$service->save();

		$service->reload();

		$this->assertEquals($service->getValueByLang('name', 'en'), 'Test service');
		$this->assertEquals($service->position, 1);
		$this->assertTrue($service->deliveryZone === $this->deliveryZone);
		$this->assertEquals($service->rangeType, ShippingService::SUBTOTAL_BASED);
	}

	public function testGetServicesByDeliveryZone()
	{
		$service1 = ShippingService::getNewInstance($this->deliveryZone, 'Test service 1', ShippingService::SUBTOTAL_BASED);
		$service1->save();
		$service2 = ShippingService::getNewInstance($this->deliveryZone, 'Test service 2', ShippingService::SUBTOTAL_BASED);
		$service2->save();

		$services = ShippingService::getByDeliveryZone($this->deliveryZone);
		$this->assertTrue($service1 === $services->get(0));
		$this->assertTrue($service2 === $services->get(1));
	}

	public function testGetServiceRates()
	{
		$service = ShippingService::getNewInstance($this->deliveryZone, 'Test service 1', ShippingService::SUBTOTAL_BASED);
		$service->save();

		$rate1 = ShippingRate::getNewInstance($service, 1.1, 1.2);
		$rate1->save();
		$rate2 = ShippingRate::getNewInstance($service, 1.3, 1.4);
		$rate2->save();

		$rates = $service->getRates();
		$this->assertTrue($rate1 === $rates->get(0));
		$this->assertTrue($rate2 === $rates->get(1));
	}
}
?>