<?php

/**
 * Top-level model class for Store related logic
 *
 * @package application.model
 * @author Saulius Rupainis <saulius@integry.net>
 *
 */
class Store
{
	private $languageList = null;
	
	/**
	 * LiveCart operates on a single store object
	 *
	 * @var Store
	 */
	private static $instance = null;

	public static function getInstance()
	{
		if (self::$instance == null)
		{
			self::$instance = new Store();
		}
		return self::$instance;
	}

	/**
	 * Gets a record set of installed languages
	 *
	 * @return ARSet
	 */
	public function getLanguageList()
	{
		if ($this->languageList == null)
		{
			ClassLoader::import("application.model.system.Language");
	
			$langFilter = new ARSelectFilter();
			$langFilter->setCondition(new EqualsCond(new ARFieldHandle("Language", "isEnabled"), 1));
			$this->languageList = ActiveRecordModel::getRecordSet("Language", $langFilter);
		}
		return $this->languageList;
	}
	
	/**
	 * Gets an installed language code array
	 * 
	 * @return array
	 */
	public function getLanguageArray($includeDefaultLanguage = false)
	{
		$langList = $this->getLanguageList();
		$langArray = array();
		$defaultLangCode = $this->getDefaultLanguageCode();
		foreach ($langList as $lang)
		{
			if ($defaultLangCode != $lang->getID() || $includeDefaultLanguage)
			{
				$langArray[] = $lang->getID();
			}
		}
		return $langArray;
	}
	
	/**
	 * Gets a code of default store language
	 *
	 * @return string
	 */
	public function getDefaultLanguageCode()
	{
		$langList = $this->getLanguageList();
		$langArray = array();
		foreach ($langList as $lang)
		{
			if ($lang->isDefault())
			{
				return $lang->getID();
			}
		}
		return false;
	}
}

?>