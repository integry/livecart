<?php

ClassLoader::import("application.model.ActiveRecordModel");
ClassLoader::import("application.model.role.*");

/**
 * Users group
 *
 * @package application.model.user
 *
 */
class UserGroup extends ActiveRecordModel 
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);

		$schema->setName("UserGroup");
		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARField("name", ARVarchar::instance(60)));
		$schema->registerField(new ARField("description", ARVarchar::instance(100)));
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
	 * Create new user group
	 * 
	 * @param DeliveryZone $deliveryZone Delivery zone instance
	 * @param Tax $tax Tax type
	 * @param float $rate Rate in percents
	 * @return TaxRate
	 */
	public static function getNewInstance($name, $description = '')
	{
	  	$instance = ActiveRecord::getNewInstance(__CLASS__);

	  	$instance->name->set($name);
	  	$instance->description->set($description);
	  	
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
	 * Load users in this group
	 *
	 * @param DeliveryZone $deliveryZone 
	 * @param bool $loadReferencedRecords
	 *
	 * @return ARSet
	 */
	public function getUsersRecordSet(ARSelectFilter $filter = null, $loadReferencedRecords = array('UserGroup'))
	{
		return User::getRecordSetByGroup($this, $filter, $loadReferencedRecords);
	}

	public function getRolesRecordSet(ARSelectFilter $filter = null, $loadReferencedRecords = false)
	{
	    if(!$filter) 
        {
            $filter = new ARSelectFilter();
        }
        
        $rolesRecordSet = new ARSet();
        
        foreach(AccessControlAssociation::getRecordSetByUserGroup($this, $filter) as $association)
        {
            $rolesRecordSet->add($association->role->get());
        }
        
        return $rolesRecordSet;
	}
}

?>
