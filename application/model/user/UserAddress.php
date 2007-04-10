<?php

/**
 * Customer billing or shipping address
 *
 * @package application.model.user
 */
class UserAddress extends ActiveRecordModel
{
    /**
     * Define database schema
     */
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);
		
		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("stateID", "state", "ID", 'State', ARInteger::instance()));
		$schema->registerField(new ARField("name", ARVarchar::instance(255)));
		$schema->registerField(new ARField("address1", ARVarchar::instance(255)));
		$schema->registerField(new ARField("address2", ARVarchar::instance(255)));
		$schema->registerField(new ARField("city", ARVarchar::instance(255)));        		
		$schema->registerField(new ARField("stateName", ARVarchar::instance(255))); 
		$schema->registerField(new ARField("postalCode", ARVarchar::instance(50))); 
		$schema->registerField(new ARField("countryID", ARChar::instance(2)));
		$schema->registerField(new ARField("phone", ARVarchar::instance(100)));
	}    
	
	public static function getNewInstance()
	{
		return parent::getNewInstance(__CLASS__);
	}
}
	
?>