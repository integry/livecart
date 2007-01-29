<?php

ClassLoader::import("application.model.ActiveRecordModel");

/**
 *
 * @package application.model.product
 */
class Manufacturer extends ActiveRecordModel
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARField("name", ARVarchar::instance(60)));
	}
}

?>
