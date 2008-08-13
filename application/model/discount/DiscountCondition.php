<?php

ClassLoader::import('application.model.system.ActiveTreeNode');
ClassLoader::import('application.model.system.MultilingualObject');

/**
 * Define database schema for Category model
 *
 * @param string $className Schema name
 */
public static function defineSchema($className = __CLASS__)
{
	$schema = self::getSchemaInstance($className);
	$schema->setName($className);
	parent::defineSchema($className);

	$schema->registerField(new ARField("isEnabled", ARBool::instance()));
	$schema->registerField(new ARField("isAnyProduct", ARBool::instance()));
	$schema->registerField(new ARField("isValidByDate", ARBool::instance()));
	$schema->registerField(new ARField("isAllSubconditions", ARBool::instance()));

	$schema->registerField(new ARField("validFrom", ARDateTime::instance()));
	$schema->registerField(new ARField("validTo", ARDateTime::instance()));

	$schema->registerField(new ARForeignKeyField("defaultImageID", "categoryImage", "ID", 'CategoryImage', ARInteger::instance()));
	$schema->registerField(new ARField("name", ARArray::instance()));
	$schema->registerField(new ARField("description", ARArray::instance()));
	$schema->registerField(new ARField("keywords", ARArray::instance()));

	$schema->registerField(new ARField("availableProductCount", ARInteger::instance()));
	$schema->registerField(new ARField("activeProductCount", ARInteger::instance()));
	$schema->registerField(new ARField("totalProductCount", ARInteger::instance()));
}

?>