<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.delivery.DeliveryZone");
ClassLoader::import("application.model.delivery.DeliveryZoneCountry");

class TestDeliveryZoneCountry extends UnitTest
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
	    
	    $deliveryCountry->markAsNotLoaded();
	    $deliveryCountry->load();
	    
	    $this->assertEqual($deliveryCountry->deliveryZone->get(), $this->zone);
	    $this->assertTrue($deliveryCountry->countryCode->get(), 'LT');
	}
	
	public function testDeleteDeliveryZoneCountry()
	{
	    $deliveryCountry = DeliveryZoneCountry::getNewInstance($this->zone, 'LT');
	    $deliveryCountry->save();
	    
	    $this->assertTrue($deliveryCountry->isExistingRecord());
	    
	    $deliveryCountry->delete();
	    $deliveryCountry->markAsNotLoaded();
	    
	    try 
        { 
            $deliveryCountry->load(); 
            $this->fail(); 
        } 
        catch(Exception $e) 
        { 
            $this->pass(); 
        }
	}
}
?>