<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.delivery.DeliveryZone");
ClassLoader::import("application.model.tax.Tax");
ClassLoader::import("application.model.tax.TaxRate");

class TestTaxRate extends UnitTest
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
        
        $this->tax = Tax::getNewInstance('test type');
        $this->tax->save();
    }
    
    public function testCreateNewTaxRate()
    {
        $taxRate = TaxRate::getNewInstance($this->deliveryZone, $this->tax, 15);
        $taxRate->save();
        
        $taxRate->markAsNotLoaded();
        $taxRate->load();
        
        $this->assertEqual($taxRate->rate->get(), 15);
        $this->assertTrue($taxRate->deliveryZone->get() === $this->deliveryZone);
        $this->assertTrue($taxRate->tax->get() === $this->tax);
    }
}
?>