<?php

/**
 * Top-level model class for Store related logic
 *
 * @package application.model.system
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

  	/**
	 * Configuration registry handler instance
	 *
	 * @var Locale
	 */
	protected $configInstance = null;

	private $requestLanguage;

	private $languageList = null;

	private $configFiles = array();

	private $currencies = null;

	private $defaultCurrency = null;

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
		unset($this->config);
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

	public function getConfigInstance()
	{
	  	return $this->config;
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

    	  	$filter = new ARSelectFilter();
			$langFilter = new ARSelectFilter();
    	  	$langFilter->setOrder(new ARFieldHandle("Language", "position"), ARSelectFilter::ORDER_ASC);
			//$langFilter->setCondition(new EqualsCond(new ARFieldHandle("Language", "isEnabled"), 1));
			$this->languageList = ActiveRecordModel::getRecordSet("Language", $langFilter);
		}
		return $this->languageList;
	}

	/**
	 * Gets an installed language code array
	 *
	 * @return array
	 */
	public function getLanguageArray($includeDefaultLanguage = false, $includeInactiveLanguages = true)
	{
		$langList = $this->getLanguageList();
		$langArray = array();
		$defaultLangCode = $this->getDefaultLanguageCode();
		foreach ($langList as $lang)
		{
			if (($defaultLangCode != $lang->getID() || $includeDefaultLanguage) &&
				(($lang->isEnabled->get() == 1) || $includeInactiveLanguages))
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

	public function setConfigFiles($fileArray)
	{
	  	$this->configFiles = $fileArray;
	}

	public function setRequestLanguage($langCode)
	{
	  	$this->requestLanguage = $langCode;
	}

	/**
	 * Returns default currency instance
	 * @return Currency default currency
	 */
	public function getDefaultCurrency()
	{
		if (!$this->defaultCurrency)
		{
		  	$this->loadCurrencyData();
		}

		return $this->defaultCurrency;
	}

	/**
	 * Returns array of enabled currency ID's (codes)
	 * @param bool $includeDefaultCurrency Whether to include default currency in the list
	 * @return array Enabled currency codes
	 */
	public function getCurrencyArray($includeDefaultCurrency = true)
	{
		if (!$this->defaultCurrency)
		{
		  	$this->loadCurrencyData();
		}

		$currArray = array();
		$defCurrency = $this->getDefaultCurrency();
		foreach ($this->currencies as $currency)
		{
			if ($defCurrency != $currency->getID() || $includeDefaultCurrency)
			{
				$currArray[] = $curr->getID();
			}
		}

		return $currArray;
	}

	/**
	 * Loads currency data from database
	 */
	private function loadCurrencyData()
	{
		ClassLoader::import("application.model.Currency");

	  	$filter = new ArSelectFilter();
	  	$filter->setCondition(new EqualsCond(new ArFieldHandle('Currency', 'isEnabled'), 1));
	  	$filter->setOrder(new ArFieldHandle('Currency', 'position'), 'ASC');
	  	$this->currencies = ActiveRecord::getRecordSet('Currency', $filter);
	  	foreach ($this->currencies as $currency)
	  	{
		    if ($currency->isDefault())
		    {
			  	$this->defaultCurrency = $currency;
			}
		}
	}

	private function loadLanguageFiles()
	{
		foreach ($this->configFiles as $file)
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

	private function loadConfig()
	{
	  	ClassLoader::import("application.model.system.Config");
		$this->config = new Config($this->configFiles);
		return $this->config;
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

		    case 'config':
		    	return $this->loadConfig();
		    break;

			default:
		    break;
		}
	}
}

?>