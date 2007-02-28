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
	
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false, $data = array())
	{
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData = false, $loadReferencedRecords = false);
	}
	
	public static function getNewInstance($name)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->name->set($name);
		return $instance;	
	}
}

?>
