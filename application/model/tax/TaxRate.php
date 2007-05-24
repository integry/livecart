<?php

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
		$schema->registerField(new ARForeignKeyField("taxID", "Tax", "ID", "Tax", ARInteger::instance()));
		
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
	 * @param Tax $tax Tax type
	 * @param float $rate Rate in percents
	 * @return TaxRate
	 */
	public static function getNewInstance(DeliveryZone $deliveryZone = null, Tax $tax, $rate)
	{
	  	$instance = ActiveRecord::getNewInstance(__CLASS__);

        if($deliveryZone)
        {
            $instance->deliveryZone->set($deliveryZone);
        }
	  	
	  	$instance->tax->set($tax);
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
	 * Load rates from known delivery zone
	 *
	 * @param DeliveryZone $deliveryZone 
	 * @param bool $loadReferencedRecords
	 *
	 * @return ARSet
	 */
	public static function getRecordSetByDeliveryZone(DeliveryZone $deliveryZone=null, $includeDisabled = true, $loadReferencedRecords = array('Tax'))
	{
 	    $filter = new ARSelectFilter();

	    
	    if(!$includeDisabled)
	    {
   		    $filter->setCondition(new EqualsCond(new ARFieldHandle('Tax', "isEnabled"), 1));
	    }
	    
 	    
		if(!$deliveryZone)
		{
		    $filter->setCondition(new IsNullCond(new ARFieldHandle(__CLASS__, "deliveryZoneID")));
		}
		else
		{
		    $filter->setCondition(new EqualsCond(new ARFieldHandle(__CLASS__, "deliveryZoneID"), $deliveryZone->getID()));
		}
		
		return self::getRecordSet($filter, $loadReferencedRecords);
	}

}

?>