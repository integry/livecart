<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.tax.Tax");
ClassLoader::import("application.model.delivery.DeliveryZone");

/**
 * @author Integry Systems
 * @package test.model.tax
 */
class TaxTest extends UnitTest
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
			'TaxRate',
			'Tax',
			'DeliveryZone'
		);
	}

	public function setUp()
	{
		parent::setUp();

		$this->deliveryZone = DeliveryZone::getNewInstance();
		$this->deliveryZone->setValueByLang('name', 'en', 'test zone');
		$this->deliveryZone->isEnabled->set(true);
		$this->deliveryZone->save();
	}

	public function testGetAllTaxes()
	{
		$allTaxesCount = Tax::getTaxes()->getTotalRecordCount();

		$taxEnabled = Tax::getNewInstance('testing');
		$taxEnabled->save();

		$taxDisabled = Tax::getNewInstance('testing');
		$taxDisabled->save();

		$this->assertEqual(Tax::getTaxes()->getTotalRecordCount(), $allTaxesCount + 2);
	}
}
?>