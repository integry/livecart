<?php

/**
 * ...
 * 
 * @package application.user.model
 * @author Saulius Rupainis <saulius@integry.net>
 *
 */
class Menu extends Tree {	
	
	public static function defineSchema($className = __CLASS__) {
		
		$schema = self::getSchemaInstance($className);
		Tree::defineSchema($className);

		$schema->setName("Menu");						
		$schema->registerField(new ARField("name", Varchar::instance(60)));		
		$schema->registerField(new ARField("text", Varchar::instance(100)));	
	}	
	
	public function showChildren($preface = "") {
	  
	  	echo $preface.$this->name->get()."<br>";	
		foreach ($this as $child) {
		  
		  	$child->showChildren($preface." &nbsp; &nbsp; ");
		}
	}
}


?>