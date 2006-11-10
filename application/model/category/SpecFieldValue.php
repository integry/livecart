<?php

ClassLoader::import("application.model.system.MultilingualObject");

/**
 * Specification field value class
 *
 * @package application.model.product
 */
class SpecFieldValue extends MultilingualObject
{

	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("SpecFieldValue");

		$schema->registerField(new ARField("value", ARArray::instance()));
		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARField("translate", ARInteger::instance(1)));
	}
}

?>