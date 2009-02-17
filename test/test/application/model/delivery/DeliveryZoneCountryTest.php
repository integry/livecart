<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.delivery.DeliveryZone");
ClassLoader::import("application.model.delivery.DeliveryZoneCountry");

/**
 *
 * @package test.model.delivery
 * @author Integry Systems
 */
class DeliveryZoneCountryTest extends LiveCartTest
{
	/**
	 * @var DeliveryZone
	 */
	private $zone;

	public function __construct()
	{
		parent::__construct('delivery zone countries tests');
	}

	public function getUsedSchemas()
	{
		return array(
			'DeliveryZone',
			'DeliveryZoneCountry'
		);
	}

	public function setUp()
	{
		parent::setUp();

		$this->zone = DeliveryZone::getNewInstance();
		$this->zone->setValueByLang('name', 'en', ':TEST_ZONE');
		$this->zone->isEnabled->set(1);
		$this->zone->isFreeShipping->set(1);
		$this->zone->save();
	}

	public function testCreateNewDeliveryZoneCountry()
	{
		$deliveryCountry = DeliveryZoneCountry::getNewInstance($this->zone, 'LT');
		$deliveryCountry->save();

		$deliveryCountry->reload();

		$this->assertEqual($deliveryCountry->deliveryZone->get(), $this->zone);
		$this->assertEqual($deliveryCountry->countryCode->get(), 'LT');
	}
}
?>