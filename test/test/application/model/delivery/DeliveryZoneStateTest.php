<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';


/**
 *
 * @package test.model.delivery
 * @author Integry Systems
 */
class DeliveryZoneStateTest extends LiveCartTest
{
	/**
	 * @var DeliveryZone
	 */
	private $zone;

	/**
	 * @var State
	 */
	private $alaska;

	public function __construct()
	{
		parent::__construct('delivery zone states tests');
	}

	public function getUsedSchemas()
	{
		return array(
			'DeliveryZone',
			'DeliveryZoneCountry',
			'DeliveryZoneState'
		);
	}

	public function setUp()
	{
		parent::setUp();

		$this->zone = DeliveryZone::getNewInstance();
		$this->zone->name->set(':TEST_ZONE');
		$this->zone->isEnabled->set(1);
		$this->zone->isFreeShipping->set(1);
		$this->zone->save();

		$this->alaska = State::getInstanceByID(1, true, true);
	}

	public function testCreateNewDeliveryZoneState()
	{
		$deliveryState = DeliveryZoneState::getNewInstance($this->zone,  $this->alaska);
		$deliveryState->save();

		$deliveryState->reload();

		$this->assertEquals($deliveryState->deliveryZone->get(), $this->zone);
		$this->assertTrue($deliveryState->state->get() === $this->alaska);
	}
}
?>