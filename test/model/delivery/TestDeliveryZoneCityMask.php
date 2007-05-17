<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.delivery.DeliveryZone");
ClassLoader::import("application.model.delivery.DeliveryZoneCityMask");

class TestDeliveryZoneCityMask extends UnitTest
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
	    $this->zone->setValueByLang('name', 'en', ':TEST_ZONE');
	    $this->zone->isEnabled->set(1);
	    $this->zone->isFreeShipping->set(1);
	    $this->zone->save();
	}
	
	public function testCreateNewDeliveryZoneCityMask()
	{
	    $cityMask = DeliveryZoneCityMask::getNewInstance($this->zone, 'Viln%');
	    $cityMask->save();
	    
	    $cityMask->reload();
	    
	    $this->assertEqual($cityMask->deliveryZone->get(), $this->zone);
	    $this->assertTrue($cityMask->mask->get(), 'Viln%');
	}
	
	public function testDeleteDeliveryZoneCityMask()
	{
	    $cityMask = DeliveryZoneCityMask::getNewInstance($this->zone, 'Viln%');
	    $cityMask->save();
	    
	    $this->assertTrue($cityMask->isExistingRecord());
	    
	    $cityMask->reload();
	    
	    try 
        { 
            $cityMask->load(); 
            $this->fail(); 
        } 
        catch(Exception $e) 
        { 
            $this->pass(); 
        }
	}
}
?>