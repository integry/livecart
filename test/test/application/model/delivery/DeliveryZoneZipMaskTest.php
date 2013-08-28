<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.delivery.DeliveryZone");
ClassLoader::import("application.model.delivery.DeliveryZoneZipMask");

/**
 *
 * @package test.model.delivery
 * @author Integry Systems
 */
class DeliveryZoneZipMaskTest extends LiveCartTest
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
			'DeliveryZoneZipMask'
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

	public function testCreateNewDeliveryZoneZipMask()
	{
		$zipMask = DeliveryZoneZipMask::getNewInstance($this->zone, 'Viln%');
		$zipMask->save();

		$zipMask->reload();

		$this->assertEquals($zipMask->deliveryZone, $this->zone);
		$this->assertEquals($zipMask->mask, 'Viln%');
	}
}
?>