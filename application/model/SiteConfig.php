<?php

ClassLoader::import("application.model.ActiveRecordModel");

/**
 * Site configuration model. 
 *
 * @package application.user.model
 * @author Denis Slaveckij <denis@integry.net>
 *
 */
class SiteConfig extends ActiveRecordModel {
	
	public static function defineSchema($className = __CLASS__) {
		
		$schema = self::getSchemaInstance($className);
		$schema->setName("SiteConfig");		
		$schema->registerField(new ARPrimaryKeyField("ID", 	Integer::instance()));
		$schema->registerField(new ARField("configData", Varchar::instance(20000)));
	}


}













?>