<?php

/**
 * ...
 * 
 * @package application.user.model
 * @author Saulius Rupainis <saulius@integry.net>
 *
 */
class RoleGroup extends Tree {	
	
	public static function defineSchema($className = __CLASS__) {
		
		$schema = self::getSchemaInstance($className);
		Tree::defineSchema($className);

		$schema->setName("RoleGroup");						
		$schema->registerField(new ARField("name", Varchar::instance(60)));		
		$schema->registerField(new ARField("description", Varchar::instance(100)));	
	}	
}


?>