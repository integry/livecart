<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.delivery.ShippingService");

class TestShippingRate extends UnitTest
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
        $this->deliveryZone->setValueByLang('name', 'en', 'test zone');
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
        
        $shippingRate->markAsNotLoaded();
        $shippingRate->load();
        
        $this->assertTrue($shippingRate->shippingService->get() === $this->shippingService);
        
        // Range start and range end can be retrived using range start and range end shortcuts or using full name getSubtotalRange* or getWeightRange*
        $this->assertEqual($shippingRate->getRangeStart(), 1.5);
        $this->assertEqual($shippingRate->getRangeEnd(), 10.5);
        $this->assertEqual($shippingRate->subtotalRangeStart->get(), $shippingRate->getRangeStart());
        $this->assertEqual($shippingRate->subtotalRangeEnd->get(), $shippingRate->getRangeEnd());
        
        $this->assertEqual($shippingRate->flatCharge->get(), 1.1);
        $this->assertEqual($shippingRate->perItemCharge->get(), 1.2);
        $this->assertEqual($shippingRate->subtotalPercentCharge->get(), 1.3);
        $this->assertEqual($shippingRate->perKgCharge->get(), 1.4);
    }

    public function testGetRatesByService()
    {
        $rate1 = ShippingRate::getNewInstance($this->shippingService, 1.1, 1.2);
        $rate1->save();
        $rate2 = ShippingRate::getNewInstance($this->shippingService, 1.3, 1.4);
        $rate2->save();
        
        $rates = ShippingRate::getRecordSetByService($this->shippingService);
        $this->assertTrue($rate1 === $rates->get(0));
        $this->assertTrue($rate2 === $rates->get(1));
    }
}
?>