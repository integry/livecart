<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.delivery.DeliveryZone");
ClassLoader::import("application.model.delivery.DeliveryZoneZipMask");

class TestDeliveryZoneZipMask extends UnitTestCase
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
        parent::__construct('delivery zone city masks tests');
        
	    $this->db = ActiveRecord::getDBConnection();
    }

    public function setUp()
	{
	    ActiveRecordModel::beginTransaction();	
	    
	    if(empty($this->autoincrements))
	    {
		    foreach(array('DeliveryZone', 'DeliveryZoneZipMask') as $table)
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

	    foreach(array('DeliveryZone', 'DeliveryZoneZipMask') as $table)
	    {
	        ActiveRecord::removeClassFromPool($table);
	        $this->db->executeUpdate("ALTER TABLE $table AUTO_INCREMENT=" . $this->autoincrements[$table]);
	    }	    
	}
	
	public function testCreateNewDeliveryZoneZipMask()
	{
	    $zipMask = DeliveryZoneZipMask::getNewInstance($this->zone, 'Viln%');
	    $zipMask->save();
	    
	    $zipMask->markAsNotLoaded();
	    $zipMask->load();
	    
	    $this->assertEqual($zipMask->deliveryZone->get(), $this->zone);
	    $this->assertTrue($zipMask->mask->get(), 'Viln%');
	}
	
	public function testDeleteDeliveryZoneZipMask()
	{
	    $zipMask = DeliveryZoneZipMask::getNewInstance($this->zone, 'Viln%');
	    $zipMask->save();
	    
	    $this->assertTrue($zipMask->isExistingRecord());
	    
	    $zipMask->delete();
	    $zipMask->markAsNotLoaded();
	    
	    try 
        { 
            $zipMask->load(); 
            $this->fail(); 
        } 
        catch(Exception $e) 
        { 
            $this->pass(); 
        }
	}
}
?>