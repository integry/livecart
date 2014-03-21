<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application/model/delivery/ShippingService");

/**
 *
 * @package test.model.delivery
 * @author Integry Systems
 */
class ShippingRateTest extends LiveCartTest
{
	/**
	 * Delivery zone
	 *
	 * @var DeliveryZone
	 */
	private $deliveryZone = null;

	/**
	 * Shipping service
	 *
	 * @var ShippingService
	 */
	private $shippingService = null;

	public function __construct()
	{
		parent::__construct('shiping rate tests');
	}

	public function getUsedSchemas()
	{
		return array(
			'ShippingService',
			'DeliveryZone',
			'ShippingRate'
		);
	}

	public function setUp()
	{
		parent::setUp();

		$this->deliveryZone = DeliveryZone::getNewInstance();
		$this->deliveryZone->name->set('test zone');
		$this->deliveryZone->save();

		$this->shippingService = ShippingService::getNewInstance($this->deliveryZone, 'test category', ShippingService::SUBTOTAL_BASED);
		$this->shippingService->save();
	}

	public function testCreateNewRate()
	{
		$shippingRate = ShippingRate::getNewInstance($this->shippingService, 1.5, 10.5);

		$shippingRate->flatCharge->set(1.1);
		$shippingRate->perItemCharge->set(1.2);
		$shippingRate->subtotalPercentCharge->set(1.3);
		$shippingRate->perKgCharge->set(1.4);
		$shippingRate->save();

		$shippingRate->reload();

		$this->assertTrue($shippingRate->shippingService === $this->shippingService);

		// Range start and range end can be retrived using range start and range end shortcuts or using full name getSubtotalRange* or getWeightRange*
		$this->assertEquals($shippingRate->getRangeStart(), 1.5);
		$this->assertEquals($shippingRate->getRangeEnd(), 10.5);
		$this->assertEquals($shippingRate->subtotalRangeStart, $shippingRate->getRangeStart());
		$this->assertEquals($shippingRate->subtotalRangeEnd, $shippingRate->getRangeEnd());

		$this->assertEquals($shippingRate->flatCharge, 1.1);
		$this->assertEquals($shippingRate->perItemCharge, 1.2);
		$this->assertEquals($shippingRate->subtotalPercentCharge, 1.3);
		$this->assertEquals($shippingRate->perKgCharge, 1.4);
	}

	public function testGetRatesByService()
	{
		$rate1 = ShippingRate::getNewInstance($this->shippingService, 1.1, 1.2);
		$rate1->save();
		$rate2 = ShippingRate::getNewInstance($this->shippingService, 1.3, 1.4);
		$rate2->save();

		$rates = ShippingRate::getRecordSetByService($this->shippingService);
		$this->assertTrue($rate1 === $rates->shift());
		$this->assertTrue($rate2 === $rates->get(1));
	}
}

?>