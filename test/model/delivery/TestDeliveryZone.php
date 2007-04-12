<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.delivery.DeliveryZone");

class TestDeliveryZone extends UnitTestCase
{
    private $autoincrements = array();
        
    /**
     * Creole database connection wrapper
     *
     * @var Connection
     */
    private $db = null;
    
    public function __construct()
    {
        parent::__construct('delivery zones tests');
        
	    $this->db = ActiveRecord::getDBConnection();
    }

    public function setUp()
	{
	    ActiveRecordModel::beginTransaction();	
	    
	    if(empty($this->autoincrements))
	    {
		    foreach(array('DeliveryZone') as $table)
		    {
				$res = $this->db->executeQuery("SHOW TABLE STATUS LIKE '$table'");
				$res->next();
				$this->autoincrements[$table] = (int)$res->getInt("Auto_increment");
		    }
	    }
	}

	public function tearDown()
	{
	    ActiveRecordModel::rollback();	

	    foreach(array('DeliveryZone') as $table)
	    {
	        ActiveRecord::removeClassFromPool($table);
	        $this->db->executeUpdate("ALTER TABLE $table AUTO_INCREMENT=" . $this->autoincrements[$table]);
	    }	    
	}
	
	public function testCreateNewDeliveryZone()
	{
	    $zone = DeliveryZone::getNewInstance();
	    $zone->setValueByLang('name', 'en', ':TEST_ZONE');
	    $zone->isEnabled->set(1);
	    $zone->isFreeShipping->set(1);
	    $zone->save();
	    
	    // Reload
	    $zone->markAsNotLoaded();
	    $zone->load();
	    
	    $name = $zone->name->get();
	    $this->assertEqual($name['en'], ':TEST_ZONE');
	    $this->assertEqual($zone->isEnabled->get(), 1);
	    $this->assertTrue($zone->isFreeShipping->get(), 1);
	}
	
	public function testDeleteGroup()
	{
	    $zone = DeliveryZone::getNewInstance();
	    $zone->setValueByLang('name', 'en', ':TEST_ZONE');
	    $zone->save();
	    
	    $this->assertTrue($zone->isExistingRecord());
	    
	    $zone->delete();
	    $zone->markAsNotLoaded();
	    
	    try 
        { 
            $zone->load(); 
            $this->fail(); 
        } 
        catch(Exception $e) 
        { 
            $this->pass(); 
        }
	}
	
	public function TestGetAllDeliveryZones() 
    {
        $zonesCount = DeliveryZone::getAll()->getTotalRecordCount();
        
	    $zone0 = DeliveryZone::getNewInstance();
	    $zone0->setValueByLang('name', 'en', ':TEST_ZONE_1');
	    $zone0->isEnabled->set(0);
	    $zone0->save();
	    
	    $zone1 = DeliveryZone::getNewInstance();
	    $zone1->setValueByLang('name', 'en', ':TEST_ZONE_2');
	    $zone1->isEnabled->set(1);
	    $zone1->save();
	    
	    $this->assertEqual(DeliveryZone::getAll()->getTotalRecordCount(), $zonesCount + 2);
	}
	
	public function TestGetEnabledDeliveryZones() 
    {
        $zonesCount = DeliveryZone::getEnabled()->getTotalRecordCount();
        
	    $zone0 = DeliveryZone::getNewInstance();
	    $zone0->setValueByLang('name', 'en', ':TEST_ZONE_1');
	    $zone0->isEnabled->set(0);
	    $zone0->save();
	    
	    $zone1 = DeliveryZone::getNewInstance();
	    $zone1->setValueByLang('name', 'en', ':TEST_ZONE_2');
	    $zone1->isEnabled->set(1);
	    $zone1->save();
	    
	    $this->assertEqual(DeliveryZone::getEnabled()->getTotalRecordCount(), $zonesCount + 1);
	}

}
?>