<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.tax.Tax");

/**
 * @author Integry Systems
 * @package test.model.tax
 */ 
class TestTax extends UnitTest
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
		$this->deliveryZone->save();
	}
	
	public function testCreateNewTax()
	{
		$tax = Tax::getNewInstance('testing');
		$tax->isEnabled->set(1);
		$tax->save();
		
		$tax->reload();
		
		$this->assertEqual($tax->getValueByLang('name', Store::getInstance()->getDefaultLanguageCode()), 'testing');
		$this->assertEqual($tax->isEnabled->get(), 1);
	}

	public function testGetAllTaxes()
	{
		$allTaxesCount = Tax::getTaxes()->getTotalRecordCount();
		
		$taxEnabled = Tax::getNewInstance('testing');
		$taxEnabled->isEnabled->set(1);
		$taxEnabled->save();
		
		$taxDisabled = Tax::getNewInstance('testing');
		$taxDisabled->isEnabled->set(0);
		$taxDisabled->save();
		
		$this->assertEqual(Tax::getTaxes()->getTotalRecordCount(), $allTaxesCount + 2);
	}
}
?>