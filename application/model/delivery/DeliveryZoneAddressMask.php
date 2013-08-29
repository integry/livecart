<?php

/**
 * Match an address to delivery zone by address mask string. For example, "5th Avenue" would match all addresses
 * within the 5th Avenue. The address mask usually has to be used together with other masks or state/country
 * rules to make sure an address from a wrong country doesn't get matched.
 *
 * @package application/model/delivery
 * @author Integry Systems <http://integry.com>
 */
class DeliveryZoneAddressMask extends ActiveRecordModel
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("DeliveryZoneAddressMask");

		public $ID;
		public $deliveryZoneID", "DeliveryZone", "ID", "DeliveryZone;
		public $mask;
	}

	/**
	 * Gets an existing record instance (persisted on a database).
	 *
	 * @param mixed $recordID
	 * @param bool $loadRecordData
	 * @param bool $loadReferencedRecords
	 * @param array $data	Record data array (may include referenced record data)
	 *
	 * @return DeliveryZoneAddressMask
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
	  	$instance = new __CLASS__();

	  	$instance->deliveryZone = $zone;
	  	$instance->mask = $mask;

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