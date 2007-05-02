<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.delivery.DeliveryZone");
ClassLoader::import("application.model.delivery.DeliveryZoneAddressMask");

class TestDeliveryZoneAddressMask extends UnitTest
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
			'DeliveryZoneAddressMask'
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
	
	public function testCreateNewDeliveryZoneAddressMask()
	{
	    $addressMask = DeliveryZoneAddressMask::getNewInstance($this->zone, 'Viln%');
	    $addressMask->save();
	    
	    $addressMask->markAsNotLoaded();
	    $addressMask->load();
	    
	    $this->assertEqual($addressMask->deliveryZone->get(), $this->zone);
	    $this->assertTrue($addressMask->mask->get(), 'Viln%');
	}
	
	public function testDeleteDeliveryZoneAddressMask()
	{
	    $addressMask = DeliveryZoneAddressMask::getNewInstance($this->zone, 'Viln%');
	    $addressMask->save();
	    
	    $this->assertTrue($addressMask->isExistingRecord());
	    
	    $addressMask->delete();
	    $addressMask->markAsNotLoaded();
	    
	    try 
        { 
            $addressMask->load(); 
            $this->fail(); 
        } 
        catch(Exception $e) 
        { 
            $this->pass(); 
        }
	}
}
?>