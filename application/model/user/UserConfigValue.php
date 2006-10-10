<?php

ClassLoader::import("application.model.ActiveRecordModel");

/**
 * User config data persistent storage class
 *
 * @package application.user.model
 * @author Saulius Rupainis <saulius@integry.net>
 *
 */
class UserConfigValue extends ActiveRecordModel
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("UserConfigValue");

		$schema->registerField(new ARPrimaryKeyField("ID", Integer::instance()));
		$schema->registerField(new ARForeignKeyField("userID", "User", "ID", "User", Integer::instance()));
		$schema->registerField(new ARField("name", Varchar::instance(25)));
		$schema->registerField(new ARField("value", Varchar::instance(100)));
	}
}

?>
