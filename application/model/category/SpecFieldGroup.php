<?php

ClassLoader::import("application.model.system.MultilingualObject");
ClassLoader::import("application.model.category.Category");

/**
 * Specification field class
 *
 * @package application.model.category
 */
class SpecFieldGroup extends MultilingualObject
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("SpecField");
		
		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("categoryID", "Category", "ID", "Category", ARInteger::instance()));
		$schema->registerField(new ARField("name", ARArray::instance()));
		$schema->registerField(new ARField("position", ARInteger::instance(2)));
	}    
}

?>