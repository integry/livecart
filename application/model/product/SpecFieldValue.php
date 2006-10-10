<?php

ClassLoader::import("application.model.MultilingualDataObject");
ClassLoader::import("application.model.product.SpecFieldValueLangData");

/**
 * Specification field value class
 *
 * @package application.model.product
 */
class SpecFieldValue extends MultilingualDataObject
{

	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("SpecFieldValue");

		$schema->registerField(new ARPrimaryKeyField("ID", Integer::instance()));
		$schema->registerField(new ARField("translate", Integer::instance(1)));
	}
}

?>
