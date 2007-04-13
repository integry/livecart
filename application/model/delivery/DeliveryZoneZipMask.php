<?php
ClassLoader::import("application.model.delivery.DeliveryZone");

/**
 * 
 *
 * @package application.model.delivery
 */
class DeliveryZoneZipMask extends ActiveRecordModel 
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("DeliveryZoneZipMask");
		
		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("deliveryZoneID", "DeliveryZone", "ID", "DeliveryZone", ARInteger::instance()));
		$schema->registerField(new ARField("mask", ARChar::instance(60)));
	}
	
	/**
	 * Gets an existing record instance (persisted on a database).
	 *
	 * @param mixed $recordID
	 * @param bool $loadRecordData
	 * @param bool $loadReferencedRecords
	 * @param array $data	Record data array (may include referenced record data)
	 *
	 * @return DeliveryZoneZipMask
	 */
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false, $data = array())
	{	
	    return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords, $data);
	}
	
	/**
	 * @return DeliveryZoneState
	 */
	public static function getNewInstance(DeliveryZone $zone, $mask)
	{
	  	$instance = ActiveRecord::getNewInstance(__CLASS__);
	  	
	  	$instance->deliveryZone->set($zone);
	  	$instance->mask->set($mask);
	  	
	  	return $instance;
	}

	/**
	 * @param DeliveryZone $zone
	 * 
	 * @return ARSet
	 */
	public static function getRecordSetByZone(DeliveryZone $zone, $loadReferencedRecords = false)
	{
	    $filter = new ARSelectFilter();
	    $filter->setCondition(new EqualsCond(new ARFieldHandle(__CLASS__, 'deliveryZoneID'), $zone->getID()));
	    
	    return self::getRecordSet(__CLASS__, $filter, $loadReferencedRecords);
	}
}

?>