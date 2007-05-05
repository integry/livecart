<?php
ClassLoader::import("application.model.system.ActiveTreeNode");
ClassLoader::import("application.model.system.MultilingualObject");
ClassLoader::import("application.model.delivery.*");
ClassLoader::import("application.model.tax.*");

/**
 * Hierarchial product category model class
 *
 * @package application.model.delivery
 */
class TaxRate extends MultilingualObject 
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("TaxRate");
		
		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("deliveryZoneID", "DeliveryZone", "ID", "DeliveryZone", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("taxTypeID", "TaxType", "ID", "TaxType", ARInteger::instance()));
		
		$schema->registerField(new ARField("rate", ARFloat::instance()));
	}

	/**
	 * Gets an existing record instance (persisted on a database).
	 * @param mixed $recordID
	 * @param bool $loadRecordData
	 * @param bool $loadReferencedRecords
	 * @param array $data	Record data array (may include referenced record data)
	 *
	 * @return TaxRate
	 */
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false, $data = array())
	{		    
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords, $data);
	}
	
	/**
	 * Create new tax rate
	 * 
	 * @param DeliveryZone $deliveryZone Delivery zone instance
	 * @param TaxType $taxType Tax type
	 * @param float $rate Rate in percents
	 * @return TaxRate
	 */
	public static function getNewInstance(DeliveryZone $deliveryZone, TaxType $taxType, $rate)
	{
	  	$instance = ActiveRecord::getNewInstance(__CLASS__);
	  	$instance->deliveryZone->set($deliveryZone);
	  	$instance->taxType->set($taxType);
	  	$instance->rate->set((int)$rate);
	  	
	  	return $instance;
	}

	/**
	 * Load service rates record set
	 *
	 * @param ARSelectFilter $filter
	 * @param bool $loadReferencedRecords
	 *
	 * @return ARSet
	 */
	public static function getRecordSet(ARSelectFilter $filter, $loadReferencedRecords = false)
	{
		return parent::getRecordSet(__CLASS__, $filter, $loadReferencedRecords);
	}
	
	/**
	 * Load service rates from known service
	 *
	 * @param DeliveryZone $deliveryZone 
	 * @param bool $loadReferencedRecords
	 *
	 * @return ARSet
	 */
	public static function getRecordSetByDeliveryZone(DeliveryZone $deliveryZone, $loadReferencedRecords = false)
	{
 	    $filter = new ARSelectFilter();

		$filter->setCondition(new EqualsCond(new ARFieldHandle(__CLASS__, "deliveryZoneID"), $deliveryZone->getID()));
		
		return self::getRecordSet($filter, $loadReferencedRecords);
	}
}

?>