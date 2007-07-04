<?php

ClassLoader::import("application.model.ActiveRecordModel");

/**
 * System language logic - adding, removing or enabling languages.
 *
 * @author Integry Systems <http://integry.com>  
 * @package application.model.system
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
	 * Gets language by its id.
	 * @param string(2) $ID
	 * @return Language
	 */
	public static function getInstanceByID($ID)
	{
		return ActiveRecord::getInstanceByID("Language", $ID, true);
	}

	public static function deleteById($id)
	{
		// make sure the currency record exists
		$inst = ActiveRecord::getInstanceById('Language', $id, true);
		
		// make sure it's not the default currency
		if (true != $inst->isDefault->get())			
		{
			ActiveRecord::deleteByID('Language', $id);
			return true;
		}
		else
		{
		  	return false;
		}
	}

	public function toArray()
	{
	  	$array = parent::toArray();
	  	$array['name'] = $this->getStore()->getLocaleInstance()->info()->getLanguageName($array['ID']);
	  	$array['originalName'] = $this->getStore()->getLocaleInstance()->info()->getOriginalLanguageName($array['ID']);
	  	
		if (file_exists(ClassLoader::getRealPath('public.image.localeflag') . '/' . $array['ID'] . '.png'))
		{
		  	$array['image'] = 'image/localeflag/' . $array['ID'] . '.png';
		}	  	
		
		return $array;
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
	
	protected function insert()
	{
	  	// get max position
	  	$f = new ARSelectFilter();
	  	$f->setOrder(new ARFieldHandle('Language', 'position'), 'DESC');
	  	$f->setLimit(1);
	  	$rec = ActiveRecord::getRecordSetArray('Language', $f);
		$position = (is_array($rec) && count($rec) > 0) ? $rec[0]['position'] + 1 : 1;

		// default new language state
		$this->setAsEnabled(0);
		$this->setAsDefault(0);
		$this->position->set($position);	  	
		
		parent::insert();
	}
}

?>