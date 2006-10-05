<?php


class LanguageData extends ActiveRecord {
      
  	/**
	 * Language Data schema definition
	 * @param string $className
	 */
	 public static function defineSchema($className = __CLASS__) {
		
		$schema = self::getSchemaInstance($className);
		$schema->setName("LanguageData");
				
		$schema->registerField(new ARPrimaryForeignKeyField("ID", "Language", "ID", "Language", Char::instance(2)));	
		$schema->registerField(new ARField("interfaceData", Varchar::instance(20000)));
	}				
}

?>