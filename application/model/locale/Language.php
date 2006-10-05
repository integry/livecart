<?php

/**
 * Class for working with language DataObject
 * @package application.model.locale 
 */
class Language extends ActiveRecordModel {
      
  	/**
	 * Languages schema definition
	 * @param string $className
	 * @todo code must be Unique
	 */
	 public static function defineSchema($className = __CLASS__) {
		
		$schema = self::getSchemaInstance($className);
		$schema->setName("Language");
		
		$schema->registerField(new ARPrimaryKeyField("ID", 	Char::instance(2)));		
		$schema->registerField(new ARField("isEnabled", Bool::instance()));
		$schema->registerField(new ARField("isDefault", Bool::instance()));
	}
			
	/**
	 * Gets languages RecordSet.
	 * @param integer $active Possible values
	 * 	0 => all, 1=>active languages, 2=> not active languages
	 * @return RecordSet
	 */	 
	public static function getLanguages($active = 0) {
	  	
	  	$filter = new ArSelectFilter();
	
	  	switch ($active) {
		 			
			case 1:
				
				$filter->setCondition(new EqualsCond(new ArFieldHandle("Language", "isEnabled"), 1));	
			break;
			
			case 2:
			
				$filter->setCondition(new EqualsCond(new ArFieldHandle("Language", "isEnabled"), 0));
			break;  
		}
	  	  	
		return ActiveRecord::getRecordSet("Language", $filter);	
	}
	
	/**	
	 * Gets default Language.
	 * @return ActiveRecord
	 */	
	public static function getDefaultLanguage() {

		$filter = new ARSelectFilter();
		$filter->setCondition(new EqualsCond(new ARFieldHandle("Language", "isDefault"), 1));
		
		$languages = Language::getRecordSet("Language", $filter, true);
		
		if (count($languages->getIterator()) == 0) {
			
			return false;			
		}
		
		return $languages->getIterator()->current(); 
	}	
	
	/**	
	 * Enables or disables language.
	 * @param string(2) $ID Language id
	 * @param bool $enabled If 1 enables language, if 0 disables
	 */
	public static function setEnabled($ID, $enabled) {
	  	  
		$lang = ActiveRecord::getInstanceByID("Language", $ID);
		$lang->isEnabled->set($enabled);
		$lang->save();	
	}
	
	/**
	 * Sets default language.
	 * @param string(2) $ID Language id
	 */
	public static function setDefault($ID) {
	  
	  	$filter = new ARUpdateFilter();
	  	$filter->addModifier("isDefault", 0);
		ActiveRecord::updateRecordSet("Language", $filter);
		
		$filter = new ARUpdateFilter();
	  	$filter->addModifier("isDefault", 1);
		$filter->setCondition(new EqualsCond(new ArFieldHandle("Language", "ID"), $ID));		
		ActiveRecord::updateRecordSet("Language", $filter);	
	}
	
	/**
	 * Gets language by it's id.
	 * @param string(2) $ID
	 * @return Language
	 */
	public static function getInstanceByID($ID) {
		
		return ActiveRecord::getInstanceByID("Language", $ID, true);	
	}
		
	/**
	 * Adds new language to database
	 * @param string(2) $ID
	 */
	public static function add($ID) {
			
		$dataEn = ActiveRecord::getInstanceByID("InterfaceTranslation", array("ID" => "en"), true);				
		$defs = unserialize($dataEn->interfaceData->Get());
		
		foreach ($defs as $key => $value) {
		  
		  	$defs[$key][Locale::value] = "";
		}	
		
	  	$lang = ActiveRecord::getNewInstance("Language");											
		$lang->setID($ID);
		$lang->isEnabled->set(0);
		$lang->isDefault->set(0);
		$lang->save(self::PERFORM_INSERT);  
		
		$data = ActiveRecord::getNewInstance("InterfaceTranslation");
		$data->setID($lang);
		$data->interfaceData->Set(addslashes(serialize($defs)));			
		$data->save(self::PERFORM_INSERT);
		
		return true;	
	}	
		
	
	
		
}


?>