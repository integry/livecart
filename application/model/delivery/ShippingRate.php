<?php
ClassLoader::import("application.model.system.ActiveTreeNode");
ClassLoader::import("application.model.system.MultilingualObject");
ClassLoader::import("application.model.delivery.*");

/**
 * Hierarchial product category model class
 *
 * @package application.model.delivery
 */
class ShippingRate extends MultilingualObject 
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("ShippingRate");
		
		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("shippingServiceID", "ShippingService", "ID", "ShippingService", ARInteger::instance()));
		
		$schema->registerField(new ARField("weightRangeStart", ARFloat::instance()));
		
		$schema->registerField(new ARField("weightRangeEnd", ARFloat::instance()));
		$schema->registerField(new ARField("subtotalRangeStart", ARFloat::instance()));
		$schema->registerField(new ARField("subtotalRangeEnd", ARFloat::instance()));
		$schema->registerField(new ARField("flatCharge", ARFloat::instance()));
		$schema->registerField(new ARField("perItemCharge", ARFloat::instance()));
		$schema->registerField(new ARField("subtotalPercentCharge", ARFloat::instance()));
		$schema->registerField(new ARField("perKgCharge", ARFloat::instance()));
	}

	/**
	 * Gets an existing record instance (persisted on a database).
	 * @param mixed $recordID
	 * @param bool $loadRecordData
	 * @param bool $loadReferencedRecords
	 * @param array $data	Record data array (may include referenced record data)
	 *
	 * @return ShippingRate
	 */
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false, $data = array())
	{		    
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords, $data);
	}
	
	/**
	 * Create new shipping rate instance
	 * 
	 * @return ShippingRate
	 */
	public static function getNewInstance()
	{
	  	return ActiveRecord::getNewInstance(__CLASS__);
	}
}

?>