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

		$schema->registerField(new ARForeignKeyField("specFieldID", "SpecField", "ID", "SpecField", ARInteger::instance()));

		$schema->registerField(new ARField("value", ARArray::instance()));
		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));

	}

	public static function getRecordSetArray($specFieldId)
	{
        $filter = new ARSelectFilter();
        $filter->setCondition(new EqualsCond(new ARFieldHandle(__CLASS__, 'specFieldID'), $specFieldId));

        return parent::getRecordSetArray(__CLASS__, $filter, false);
	}

}

?>