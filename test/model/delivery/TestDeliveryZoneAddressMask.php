<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.delivery.DeliveryZone");
ClassLoader::import("application.model.delivery.DeliveryZoneAddressMask");

class TestDeliveryZoneAddressMask extends UnitTestCase
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
		    foreach(array('DeliveryZone', 'DeliveryZoneAddressMask') as $table)
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

	    foreach(array('DeliveryZone', 'DeliveryZoneAddressMask') as $table)
	    {
	        ActiveRecord::removeClassFromPool($table);
	        $this->db->executeUpdate("ALTER TABLE $table AUTO_INCREMENT=" . $this->autoincrements[$table]);
	    }	    
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