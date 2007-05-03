<?php
ClassLoader::import("application.model.system.ActiveTreeNode");
ClassLoader::import("application.model.system.MultilingualObject");
ClassLoader::import("application.model.delivery.*");

/**
 * Hierarchial product category model class
 *
 * @package application.model.delivery
 */
class ShippingService extends MultilingualObject 
{
    const WEIGHT_BASED = 0;
    const SUBTOTAL_BASED = 1;
    
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("ShippingService");
		
		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("deliveryZoneID", "DeliveryZone", "ID", "DeliveryZone", ARInteger::instance()));
		$schema->registerField(new ARField("name", ARArray::instance()));
		$schema->registerField(new ARField("position", ARInteger::instance(10)));
		$schema->registerField(new ARField("rangeType", ARInteger::instance(1)));
	}

	/**
	 * Gets an existing record instance
	 * 
	 * @param mixed $recordID
	 * @param bool $loadRecordData
	 * @param bool $loadReferencedRecords
	 * @param array $data	Record data array (may include referenced record data)
	 *
	 * @return ShippingService
	 */
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false, $data = array())
	{		    
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords, $data);
	}
	
	/**
	 * Create new shipping service
	 * 
	 * @param DeliveryZone $deliveryZone Delivery zone
	 * @param string $defaultLanguageName Service name in default language
	 * @param integer $calculationCriteria Shipping price calculation criteria. 0 for weight based calculations, 1 for subtotal based calculations
	 * @return ShippingService
	 */
	public static function getNewInstance(DeliveryZone $deliveryZone, $defaultLanguageName, $calculationCriteria)
	{
        $instance = parent::getNewInstance(__CLASS__);
        $instance->deliveryZone->set($deliveryZone);
        $instance->setValueByLang('name', Store::getInstance()->getDefaultLanguageCode(), $defaultLanguageName);
        $instance->rangeType->set($calculationCriteria);
        
        return $instance;
	}

	/**
	 * Load delivery services record set
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
	 * Load delivery services record by Delivery zone
	 *
	 * @param ARSelectFilter $filter
	 * @param bool $loadReferencedRecords
	 *
	 * @return ARSet
	 */
	public static function getByDeliveryZone(DeliveryZone $deliveryZone, $loadReferencedRecords = false)
	{
 	    $filter = new ARSelectFilter();

		$filter->setOrder(new ARFieldHandle(__CLASS__, "position"), 'ASC');
		$filter->setCondition(new EqualsCond(new ARFieldHandle(__CLASS__, "deliveryZoneID"), $deliveryZone->getID()));
		
		return self::getRecordSet($filter, $loadReferencedRecords);
	}
}

?>