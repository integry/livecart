<?php

ClassLoader::import('application.model.ActiveRecordModel');
ClassLoader::import('application.model.eav.EavAble');

/**
 * Defines a product manufacturer. Each product can be assigned to one manufacturer.
 * Keeping manufacturers as a separate entity allows to manipulate them more easily and
 * provide more effective product filtering (search by manufacturers).
 *
 * @package application.model.product
 * @author Integry Systems <http://integry.com>
 */
class Manufacturer extends ActiveRecordModel implements EavAble
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARField("name", ARVarchar::instance(60)));
	}

	public static function getNewInstance($name)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->name->set($name);
		return $instance;
	}

	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false, $data = array())
	{
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData = false, $loadReferencedRecords = false);
	}

	public static function getInstanceByName($name)
	{
		$filter = new ARSelectFilter();
		$filter->setCondition(new EqualsCond(new ARFieldHandle('Manufacturer', 'name'), $name));
		$filter->setLimit(1);
		$set = ActiveRecordModel::getRecordSet('Manufacturer', $filter);
		if ($set->size() > 0)
		{
			return $set->get(0);
		}
		else
		{
			return self::getNewInstance($name);
		}
	}
}

?>