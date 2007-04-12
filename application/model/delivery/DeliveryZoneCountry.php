<?php
ClassLoader::import("application.model.delivery.DeliveryZone");

/**
 * 
 *
 * @package application.model.delivery
 */
class DeliveryZoneCountry extends MultilingualObject 
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("DeliveryZoneCountry");
		
		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("deliveryZoneID", "DeliveryZone", "ID", "DeliveryZone", ARInteger::instance()));
		$schema->registerField(new ARField("countryCode", ARChar::instance(2)));
	}

	/**
	 * @return DeliveryZoneCountry
	 */
	public static function getNewInstance(DeliveryZone $zone, $countryCode)
	{
	  	$instance = ActiveRecord::getNewInstance(__CLASS__);
	  	
	  	$instance->deliveryZone->set($zone);
	  	$instance->countryCode->set($countryCode);
	  	
	  	return $instance;
	}
}

?>