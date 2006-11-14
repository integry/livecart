<?php

/**
 * Class for working with language DataObject
 * @author Denis Slaveckij 
 * @author Rinalds Uzkalns
 * @package application.model.locale 
 */
class Language extends ActiveRecordModel 
{
   	/**
	 * Languages schema definition
	 * @param string $className
	 * @todo code must be Unique
	 */
	public static function defineSchema($className = __CLASS__) 
	{		
		$schema = self::getSchemaInstance($className);
		$schema->setName("Language");
		
		$schema->registerField(new ARPrimaryKeyField("ID", ARChar::instance(2)));		
		$schema->registerField(new ARField("isEnabled", ARBool::instance()));
		$schema->registerField(new ARField("isDefault", ARBool::instance()));
		$schema->registerField(new ARField("position", ARInteger::instance()));
	}
			
	/**
	 * Gets language by it's id.
	 * @param string(2) $ID
	 * @return Language
	 */
	public static function getInstanceByID($ID) 
	{		
		return ActiveRecord::getInstanceByID("Language", $ID, true);	
	}			
			
	/**
	 * Gets languages RecordSet.
	 * @param integer $active Possible values
	 * 	0 => all, 1 => active (enabled) languages, 2 => inactive languages
	 * @return RecordSet
	 */	 
	public static function getLanguages($active = 0) 
	{	  	
	  	$filter = new ArSelectFilter();
	  	$filter->setOrder(new ArFieldHandle("Language", "position"), ArSelectFilter::ORDER_ASC);
	
		if ($active > 0)
		{		  
			$filter->setCondition(new EqualsCond(new ArFieldHandle("Language", "isEnabled"), ($active == 1 ? 1 : 0)));						 	 
		}
	
		return ActiveRecord::getRecordSet("Language", $filter);	
	}
	
	/**	
	 * Gets default Language.
	 * @return ActiveRecord
	 */	
	public static function getDefaultLanguage() 
	{
		$filter = new ARSelectFilter();
		$filter->setCondition(new EqualsCond(new ARFieldHandle("Language", "isDefault"), 1));
		
		$languages = Language::getRecordSet("Language", $filter, true);
		
		if (count($languages->getIterator()) == 0)
		{	
			return false;			
		}
		
		return $languages->getIterator()->current(); 
	}	
	
	/**	
	 * Enables or disables language.
	 * @param string(2) $ID Language id
	 * @param bool $enabled If 1 enables language, if 0 disables
	 */
	public static function setEnabled($ID, $enabled) 
	{	  	  
		$lang = ActiveRecord::getInstanceByID("Language", $ID);
		$lang->isEnabled->set((int)$enabled);
		$lang->save();	
	}
	
	/**
	 * Sets default language.
	 * @param string(2) $ID Language id
	 */
	public static function setDefault($ID) 
	{	  
	  	$filter = new ARUpdateFilter();
	  	$filter->addModifier("isDefault", 0);
		ActiveRecord::updateRecordSet("Language", $filter);
		
		$filter = new ARUpdateFilter();
	  	$filter->addModifier("isDefault", 1);
		$filter->setCondition(new EqualsCond(new ArFieldHandle("Language", "ID"), $ID));		
		ActiveRecord::updateRecordSet("Language", $filter);	
	}
			
	/**
	 * Adds new language to database
	 * @param string(2) $ID
	 * 
	 * @deprecated
	 */
	public static function add($ID) 
	{			
	  	// get max position
	  	$f = new ARSelectFilter();
	  	$f->setOrder(new ARFieldHandle('Language', 'position'), 'DESC');
	  	$f->setLimit(1);
	  	$rec = ActiveRecord::getRecordSetArray('Language', $f);	  
		$position = (is_array($rec) && count($rec) > 0) ? $rec[0]['position'] + 1 : 1;
			  	
	  	$lang = ActiveRecord::getNewInstance("Language");											
		$lang->setID($ID);
		$lang->isEnabled->set(0);
		$lang->isDefault->set(0);
		$lang->position->set($position);
		$lang->save(self::PERFORM_INSERT);  
		
		return true;	
	}
	
	
	/**
	 * Checks whether the language is systems default language
	 * @return bool
	 */
	public function isDefault()
	{
	  	return (bool)$this->isDefault->get();
	}
		
	/**
	 * Changes default language status
	 * @param bool $isDefault (sets as default if true, unsets default status if false)
	 */
	public function setAsDefault($isDefault = 1)
	{
	  	$this->isDefault->set($isDefault == 1 ? 1 : 0);
	  	return true;
	}

	/**
	 * Changes language status to enabled or disabled
	 * @param bool $isEnabled (sets as enabled if true, unsets enabled status if false)
	 */
	public function setAsEnabled($isEnabled = 1)
	{
	  	$this->isEnabled->set($isEnabled == 1 ? 1 : 0);
	  	return true;
	}
}

?>