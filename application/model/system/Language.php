<?php

namespace system;

/**
 * System language logic - adding, removing or enabling languages.
 *
 * @author Integry Systems <http://integry.com>
 * @package application.model.system
 */
class Language extends \ActiveRecordModel
{
	public $ID;
	public $isEnabled;
	public $isDefault;
	public $position;

	/**
	 * Gets language by its id.
	 * @param string(2) $ID
	 * @return Language
	 */
	public static function getInstanceByID($ID)
	{
		return ActiveRecord::getInstanceByID("Language", $ID, true);
	}

	/**
	 * Checks whether the language is systems default language
	 * @return bool
	 */
	public function isDefault()
	{
	  	return (bool)$this->isDefault;
	}

	/**
	 * Changes default language status
	 * @param bool $isDefault (sets as default if true, unsets default status if false)
	 */
	public function setAsDefault($isDefault = 1)
	{
	  	$this->isDefault = $isDefault == 1 ? 1 : 0;
	  	return true;
	}

	/**
	 * Changes language status to enabled or disabled
	 * @param bool $isEnabled (sets as enabled if true, unsets enabled status if false)
	 */
	public function setAsEnabled($isEnabled = 1)
	{
	  	$this->isEnabled = $isEnabled == 1 ? 1 : 0;
	  	return true;
	}

	public function _save()
	{
		self::deleteCache();

		return parent::save($forceOperation);
	}

	public static function deleteById($id)
	{
		self::deleteCache();

		// make sure the language record exists
		$inst = ActiveRecord::getInstanceById('Language', $id, true);

		// make sure it's not the default currency
		if (true != $inst->isDefault)
		{
			ActiveRecord::deleteByID('Language', $id);
			return true;
		}
		else
		{
		  	return false;
		}
	}

	public static function deleteCache($di)
	{
		$di->get('cache')->delete('languageList');
	}

	public static function getLanguageList($di)
	{
		$languages = self::query()->order('position')->execute(array("cache" => array("key" => "languages")));
		//$languages = self::find();

		if (!count($languages))
		{
			$lang = new Language();
			$lang->ID = en;
			$lang->isEnabled = true;
			$lang->isDefault = true;
			$languages[] = $lang;
		}

		return $languages;
	}

	protected function insert()
	{
		$this->setLastPosition();

		parent::insert();
	}

	public function toArray($args = null)
	{
	  	$array = parent::toArray();

	  	$info = self::getApplication()->getLocale()->info();
		$array['name'] = $info->getLanguageName($array['ID']);
	  	$array['originalName'] = $info->getOriginalLanguageName($array['ID']);

		if (file_exists(ClassLoader::getRealPath('public.image.localeflag') . '/' . $array['ID'] . '.png'))
		{
		  	$array['image'] = 'image/localeflag/' . $array['ID'] . '.png';
		}

		return $array;
	}
}

?>