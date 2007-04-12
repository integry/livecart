<?php
ClassLoader::import("application.model.delivery.DeliveryZoneCountry");
ClassLoader::import("application.model.delivery.DeliveryZone");

/**
 * 
 *
 * @package application.model.delivery
 */
class DeliveryZoneState extends MultilingualObject 
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("DeliveryZoneState");
		
		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("deliveryZoneID", "DeliveryZone", "ID", "DeliveryZone", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("stateID", "State", "ID", "State", ARInteger::instance()));
	}

	/**
	 * @return DeliveryZoneState
	 */
	public static function getNewInstance(DeliveryZone $zone, State $state)
	{
	  	$instance = ActiveRecord::getNewInstance(__CLASS__);
	  	
	  	$instance->deliveryZone->set($zone);
	  	$instance->state->set($state);
	  	
	  	return $instance;
	}
}

?>