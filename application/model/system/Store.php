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
	 * Gets a list of languages that are being used in a store
	 *
	 * @return array
	 */
	public function getLanguageList()
	{
		ClassLoader::import("application.model.Language");

		$langFilter = new ARSelectFilter();
		$langFilter->setCondition(new EqualsCond(new ARFieldHandle("Language", "isEnabled"), 1));
		$languageList = ActiveRecord::getRecordSetArray("Language", $langFilter);
		return $languageList;
	}
}

?>