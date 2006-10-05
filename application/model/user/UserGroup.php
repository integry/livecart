<?php


ClassLoader::import("application.model.ActiveRecordModel");

/** 
 * @package application.user.model
 * @author Saulius Rupainis <saulius@integry.net>
 *
 */
class UserGroup extends ActiveRecordModel {
	
	public static function defineSchema($className = __CLASS__) {
		
		$schema = self::getSchemaInstance($className);
		$schema->setName("UserGroup");
		
		//$schema->registerField(new ARPrimaryKeyField("ID", 	Integer::instance()));
		$schema->registerField(new ARPrimaryForeignKeyField("userID", "User", "ID", "User", Integer::instance()));
		$schema->registerField(new ARPrimaryForeignKeyField("roleGroupID", "RoleGroup", "ID", "RoleGroup", Integer::instance()));	
	}
}

?>