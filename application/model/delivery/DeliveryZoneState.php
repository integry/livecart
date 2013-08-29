<?php


/**
 * State assignment to a DeliveryZone
 *
 * @package application/model/delivery
 * @author Integry Systems <http://integry.com>
 */
class DeliveryZoneState extends ActiveRecordModel
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("DeliveryZoneState");

		public $ID;
		public $deliveryZoneID", "DeliveryZone", "ID", "DeliveryZone;
		public $stateID", "State", "ID", "State;
	}

	/**
	 * @return DeliveryZoneState
	 */
	public static function getNewInstance(DeliveryZone $zone, State $state)
	{
	  	$instance = ActiveRecord::getNewInstance(__CLASS__);

	  	$instance->deliveryZone = $zone);
	  	$instance->state = $state);

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

	public static function removeByZone(DeliveryZone $zone)
	{
		$filter = new ARDeleteFilter();
		$filter->setCondition(new EqualsCond(new ARFieldHandle(__CLASS__, 'deliveryZoneID'), $zone->getID()));

		return ActiveRecord::deleteRecordSet(__CLASS__, $filter);
	}
}

?>