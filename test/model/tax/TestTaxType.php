<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.tax.TaxType");

class TestTaxType extends UnitTest
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
			'TaxType',
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
    
    public function testCreateNewTaxType()
    {
        $taxType = TaxType::getNewInstance('testing');
        $taxType->isEnabled->set(1);
        $taxType->isShippingAddressBased->set(1);
        $taxType->save();
        
        $taxType->markAsNotLoaded();
        $taxType->load();
        $this->assertEqual($taxType->getValueByLang('name', Store::getInstance()->getDefaultLanguageCode()), 'testing');
        $this->assertEqual($taxType->isEnabled->get(), 1);
        $this->assertEqual($taxType->isShippingAddressBased->get(), 1);
    }
}
?>