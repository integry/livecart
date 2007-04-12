<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.delivery.DeliveryZone");
ClassLoader::import("application.model.delivery.DeliveryZoneCountry");

class TestDeliveryZoneCountry extends UnitTestCase
{
    private $autoincrements = array();

    /**
     * @var DeliveryZone
     */
    private $zone;
    
    /**
     * Creole database connection wrapper
     *
     * @var Connection
     */
    private $db = null;
    
    public function __construct()
    {
        parent::__construct('delivery zone countries tests');
        
	    $this->db = ActiveRecord::getDBConnection();
    }

    public function setUp()
	{
	    ActiveRecordModel::beginTransaction();	
	    
	    if(empty($this->autoincrements))
	    {
		    foreach(array('DeliveryZone', 'DeliveryZoneCountry') as $table)
		    {
				$res = $this->db->executeQuery("SHOW TABLE STATUS LIKE '$table'");
				$res->next();
				$this->autoincrements[$table] = (int)$res->getInt("Auto_increment");
		    }
	    }
	    
	    $this->zone = DeliveryZone::getNewInstance();
	    $this->zone->setValueByLang('name', 'en', ':TEST_ZONE');
	    $this->zone->isEnabled->set(1);
	    $this->zone->isFreeShipping->set(1);
	    $this->zone->save();
	}

	public function tearDown()
	{
	    ActiveRecordModel::rollback();	

	    foreach(array('DeliveryZone', 'DeliveryZoneCountry') as $table)
	    {
	        ActiveRecord::removeClassFromPool($table);
	        $this->db->executeUpdate("ALTER TABLE $table AUTO_INCREMENT=" . $this->autoincrements[$table]);
	    }	    
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