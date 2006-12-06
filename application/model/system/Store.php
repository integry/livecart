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
	 * Locale instance that application operates on
	 *
	 * @var Locale
	 */
	protected $locale = null;

	protected $localeName;

	private $requestLanguage;

	private $languageList = null;

	private $languageFiles = array();

	/**
	 * LiveCart operates on a single store object
	 *
	 * @var Store
	 */
	private static $instance = null;

	private function __construct()
	{
		// unset locale variables to make use of lazy loading
		unset($this->locale);
		unset($this->localeName);

		// get current (request) language
		$router = Router::getInstance();
//		$router->addStaticUrlParam('language', $this->localeName);

	}

	/**
	 * Store instance
	 *
	 * @return Store
	 */
	public static function getInstance()
	{
		if (self::$instance == null)
		{
			self::$instance = new Store();
		}
		return self::$instance;
	}

	public function getLocaleInstance()
	{
	  	return $this->locale;
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

    	  	$filter = new ArSelectFilter();
			$langFilter = new ARSelectFilter();
    	  	$langFilter->setOrder(new ArFieldHandle("Language", "position"), ArSelectFilter::ORDER_ASC);
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

	/**
	 * Translates text using Locale::LCInterfaceTranslator
	 * @param string $key
	 * @return string
	 */
	public function translate($key)
	{
		return $this->locale->translator()->translate($key);
	}

	/**
	 * Performs MakeText translation using Locale::LCInterfaceTranslator
	 * @param string $key
	 * @param array $params
	 * @return string
	 */
	public function makeText($key, $params)
	{
		return $this->locale->translator()->makeText($key, $params);
	}

	/**
	 * Creates a handle string that is usually used as part of URL to uniquely
	 * identify some record
	 * Example:
	 * @param string $str
	 * @return unknown
	 */
	public function createHandleString($str)
	{
		$str = strtolower(trim(strip_tags(stripslashes($str))));
		$str = str_replace(" ", "_", $str);
		return $str;
	}

	public function setLanguageFiles($fileArray)
	{
	  	$this->languageFiles = $fileArray;
	}

	public function setRequestLanguage($langCode)
	{
	  	$this->requestLanguage = $langCode;
	  	//die($langCode);
	}

	private function loadLanguageFiles()
	{
		foreach ($this->languageFiles as $file)
		{
			$this->locale->translationManager()->loadFile($file);
		}
	}

	private function loadLocale()
	{
		$this->locale =	Locale::getInstance($this->localeName);
		$this->locale->translationManager()->setCacheFileDir(ClassLoader::getRealPath('cache.language'));
		$this->locale->translationManager()->setDefinitionFileDir(ClassLoader::getRealPath('application.configuration.language'));
		Locale::setCurrentLocale($this->localeName);

		$this->loadLanguageFiles();

		return $this->locale;
	}

	private function loadLocaleName()
	{
		if ($this->requestLanguage)
		{
			$this->localeName = $this->requestLanguage;
		}
		else if (isset($_SESSION['lang']))
		{
			$this->localeName = $_SESSION['lang'];
		}
		else
		{
	  		$this->localeName = $this->getDefaultLanguageCode();
		}

		return $this->localeName;
	}

	private function __get($name)
	{
		switch ($name)
	  	{
		    case 'locale':
		    	return $this->loadLocale();
		    break;

		    case 'localeName':
		    	return $this->loadLocaleName();
		    break;

			default:
		    break;
		}
	}

}

?>