<?php
ClassLoader::import("application.model.system.ActiveTreeNode");
ClassLoader::import("application.model.system.MultilingualObject");
ClassLoader::import("application.model.category.CategoryImage");

/**
 * Hierarchial product category model class
 *
 * @package application.model.delivery
 */
class DeliveryZone extends MultilingualObject 
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("DeliveryZone");
		
		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARField("name", ARArray::instance()));
		$schema->registerField(new ARField("isEnabled", ARInteger::instance(1)));
		$schema->registerField(new ARField("isFreeShipping", ARInteger::instance(1)));
	}

	/**
	 * @return DeliveryZone
	 */
	public static function getNewInstance()
	{
	  	return ActiveRecord::getNewInstance(__CLASS__);
	}
	
	/**
	 * @return ARSet
	 */
	public static function getAll()
	{
	    $filter = new ARSelectFilter();
	    return self::getRecordSet(__CLASS__, $filter);
	}

	/**
	 * @return ARSet
	 */
	public static function getEnabled()
	{
	    $filter = new ARSelectFilter();
	    $filter->setCondition(new EqualsCond(new ArFieldHandle(__CLASS__, "isEnabled"), 1));
	    	    
	    return self::getRecordSet(__CLASS__, $filter);
	}
}

?>