<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.delivery.DeliveryZone");
ClassLoader::import("application.model.delivery.DeliveryZoneCityMask");

/**
 *
 * @package test.model.delivery
 * @author Integry Systems
 */
class DeliveryZoneCityMaskTest extends LiveCartTest
{
	/**
	 * @var DeliveryZone
	 */
	private $zone;

	public function __construct()
	{
		parent::__construct('delivery zone city masks tests');
	}

	public function getUsedSchemas()
	{
		return array(
			'DeliveryZone',
			'DeliveryZoneCityMask',
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
	}

	public function testCreateNewDeliveryZoneCityMask()
	{
		$cityMask = DeliveryZoneCityMask::getNewInstance($this->zone, 'Viln%');
		$cityMask->save();

		$cityMask->reload();

		$this->assertEquals($cityMask->deliveryZone, $this->zone);
		$this->assertEquals($cityMask->mask, 'Viln%');
	}
}
?>