<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.delivery.State");
ClassLoader::import("application.model.delivery.DeliveryZone");
ClassLoader::import("application.model.delivery.DeliveryZoneCountry");
ClassLoader::import("application.model.delivery.DeliveryZoneState");

class TestDeliveryZoneState extends UnitTest
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
	    $this->zone->setValueByLang('name', 'en', ':TEST_ZONE');
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
	    
	    $this->assertEqual($deliveryState->deliveryZone->get(), $this->zone);
	    $this->assertTrue($deliveryState->state->get() === $this->alaska);
	}
	
	public function testDeleteDeliveryZoneState()
	{
	    $deliveryState = DeliveryZoneState::getNewInstance($this->zone,  $this->alaska);
	    $deliveryState->save();
	    
	    $this->assertTrue($deliveryState->isExistingRecord());
	    
	    $deliveryState->delete();
	    $deliveryState->markAsNotLoaded();
	    
	    try 
        { 
            $deliveryState->load(); 
            $this->fail(); 
        } 
        catch(Exception $e) 
        { 
            $this->pass(); 
        }
	}
}
?>