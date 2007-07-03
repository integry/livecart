<?php

/**
 * Top-level model class for Store related logic. One of the main classes for retrieving system data - 
 * available languages, currencies, payment handlers, shipping services, etc.
 *
 * @author Integry Systems <http://integry.com>  
 * @package application.model.system
 */
class Store
{
  	/**
	 * Locale instance that application operates on
	 *
	 * @var Locale
	 */
	protected $locale = null;

  	/**
	 * Current locale code (ex: lt, en, de)
	 *
	 * @var string
	 */
	protected $localeName;

  	/**
	 * Default (base) language code/ID (ex: lt, en, de)
	 *
	 * @var string
	 */
	protected $defaultLanguageID;

  	/**
	 * Configuration registry handler instance
	 *
	 * @var Config
	 */
	protected $config = null;

	private $requestLanguage;

	private $languageList = null;

	private $configFiles = array();

	private $currencies = null;

	private $defaultCurrency = null;
	
	private $defaultCurrencyCode = null;

	private $currencyArray;
	
	private $currencySet;
	
	private $session;
	
	const EXCLUDE_DEFAULT_CURRENCY = false;

	const INCLUDE_DEFAULT = true;

	/**
	 * LiveCart operates on a single store object
	 *
	 * @var Store
	 */
	private static $instance = null;

	private static $isCustomizationMode = null;

	private static $isTranslationMode = null;

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

	public static function isCustomizationMode()
	{
		if (is_null(self::$isCustomizationMode))
		{
			$session = Session::getInstance();
			self::$isCustomizationMode = $session->getValue('customizationMode');
		}	
		
		return self::$isCustomizationMode;
	}

	public static function isTranslationMode()
	{
		if (is_null(self::$isTranslationMode))
		{
			$session = Session::getInstance();
			self::$isTranslationMode = $session->getValue('translationMode');
		}	
		
		return self::$isTranslationMode;
	}

	/**
	 * @return Locale
	 */
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
			$this->languageList = ActiveRecordModel::getRecordSet("Language", $langFilter);
		}
		return $this->languageList;
	}

	/**
	 * Returns an array represantion of installed languages
	 *
	 * @return array
	 */
	public function getLanguageSetArray($includeDefaultLanguage = false, $includeDisabledLanguages = true)
	{
        $ret = $this->languageList->toArray();
        
        if (!$includeDefaultLanguage)
        {
            $defLang = $this->getDefaultLanguageCode();
            
            foreach ($ret as $key => $data)
            {
                if ($data['ID'] == $defLang || (!$includeDisabledLanguages && $data['isEnabled'] == 0))
                {
                    unset($ret[$key]);
                }
            }
        }
        
        return $ret;
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
		if (!$this->defaultLanguageCode)
		{
			$langList = $this->getLanguageList();
			$langArray = array();
			foreach ($langList as $lang)
			{
				if ($lang->isDefault())
				{
					$this->defaultLanguageCode = $lang->getID();
				}
			}			
		}

		return $this->defaultLanguageCode;
	}
	
	/**
	 * Returns active language/locale code (ex: en, lt, de)
	 *
	 * @return string
	 */
	public function getLocaleCode()
	{
	  	return $this->localeName;
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

	public function getEnabledCountries()
	{
		$countries = $this->locale->info()->getAllCountries();
		$enabled = Config::getInstance()->getValue('ENABLED_COUNTRIES');
		
		$countries = array_intersect_key($countries, $enabled);
		
		return $countries;
	}

	public function isValidCountry($countryCode)
	{
		$enabled = Config::getInstance()->getValue('ENABLED_COUNTRIES');
		return isset($enabled[$countryCode]);		
	}

	/**
	 * Creates a handle string that is usually used as part of URL to uniquely
	 * identify some record
	 * Example: "Some Record TITLE!!!" becomes "some-record-title"
	 * @param string $str
	 * @return string
	 *
	 * @todo test with multibyte strings
	 */
	public static function createHandleString($str)
	{
		$wordSeparator = '.';
		
		$str = strtolower(trim(strip_tags(stripslashes($str))));		

		// fix accented characters
        $from = array();
		for ($k = 192; $k <= 255; $k++) 
        {
			$from[] = chr($k);
		}

		$repl = array ('A','A','A','A','A','A','A','E','E','E','E','E','I','I','I','I','D','N','O','O','O','O','O','O','O','U','U','U','U','Y','b','b','a','a','a','a','a','a','a','e','e','e','e','e','i','i','i','i','n','n','o','o','o','o','o','o','o','u','u','u','u','y','y','y');		

        $str = str_replace($from, $repl, $str);
		
		// non alphanumeric characters
		$str = preg_replace('/[^a-z0-9]/', $wordSeparator, $str);
		
		// double separators
		$str = preg_replace('/[\\' . $wordSeparator . ']{2,}/', $wordSeparator, $str);
		
        // separators from beginning and end
		$str = preg_replace('/^[\\' . $wordSeparator . ']/', '', $str);
		$str = preg_replace('/[\\' . $wordSeparator . ']$/', '', $str);
				        
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
	 * Returns default currency code
	 * @return String Default currency code/ID
	 */
	public function getDefaultCurrencyCode()
	{
		if (!$this->defaultCurrencyCode)
		{
			$this->defaultCurrencyCode = $this->getDefaultCurrency()->getID();
		}

		return $this->defaultCurrencyCode;
	}
	
	/**
	 * Returns array of enabled currency ID's (codes)
	 * @param bool $includeDefaultCurrency Whether to include default currency in the list
	 * @return array Enabled currency codes
	 */
	public function getCurrencyArray($includeDefaultCurrency = true)
	{
		$defaultCurrency = $this->getDefaultCurrencyCode();
		
		$currArray = array_flip(array_keys($this->currencies));
		
		if (!$includeDefaultCurrency)
		{
			unset($currArray[$defaultCurrency]);
		}
		
		return array_flip($currArray);
	}

	/**
	 * Returns an array of enabled currency instances
	 *
	 * @param bool $includeDefaultCurrency Whether to include default currency in the list
	 * @return array Enabled currency codes
	 */
	public function getCurrencySet($includeDefaultCurrency = true)
	{
		$defaultCurrency = $this->getDefaultCurrencyCode();
		
		$currArray = $this->currencies;

		if (!$includeDefaultCurrency)
		{
			unset($currArray[$defaultCurrency]);
		}

		return $currArray;
	}
	
	/**
	 * Returns an array of available credit card handlers
	 */
	public function getCreditCardHandlerList()
	{
		ClassLoader::import('library.payment.PaymentMethodManager');		
		return PaymentMethodManager::getCreditCardHandlerList();
	}

	/**
	 * Returns an instance of the selected credit card handler
	 */
	public function getCreditCardHandler(TransactionDetails $details = null)
	{
		$handler = $this->config->getValue('CC_HANDLER');
		
		ClassLoader::import('library.payment.method.cc.' . $handler . '.' . $handler);

		return $this->getPaymentHandler($handler, $details);
	}

    public function getPaymentHandler($className, TransactionDetails $details = null)
    {
        if (!class_exists($className, false))
        {
            ClassLoader::import('library.payment.method.' . $className . '.' . $className); 
        }   

		if (is_null($details))
		{
			$details = new TransactionDetails();
		}

		$inst = new $className($details);

		$c = $this->config->getSection('payment/' . $className);
		foreach ($c as $key => $value)
		{
			$value = $this->config->getValue($key);
			$key = substr($key, strlen($className) + 1);
			$inst->setConfigValue($key, $value);
		}

        return $inst;
    }

    public function getCardTypes(CreditCardPayment $handler)
    {
		$types = array_keys($this->config->getValue(get_class($handler) . '_cardTypes'));
		return array_combine($types, $types);
    }

	/**
	 * Returns an array of all real-time shipping rate services
	 */
	public function getAllRealTimeShippingServices()
	{
		ClassLoader::import('library.shipping.ShippingMethodManager');
		return ShippingMethodManager::getHandlerList();
	}

	/**
	 * Returns an array of enabled real-time shipping rate services
	 */
	public function getEnabledRealTimeShippingServices()
	{
		return array_flip($this->config->getValue('SHIPPING_HANDLERS'));
	}

	/**
	 * Returns a shipping handler instance
	 */
    public function getShippingHandler($className)
    {
		ClassLoader::import('library.shipping.method.' . $className);

		$inst = new $className();
		
		$c = $this->config->getSection('shipping/' . $className);
		foreach ($c as $key => $value)
		{
			$value = $this->config->getValue($key);
            $key = substr($key, strlen($className) + 1);
			$inst->setConfigValue($key, $value);
		}
		
		return $inst;        
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
	  	$currencies = ActiveRecord::getRecordSet('Currency', $filter);
	  	$this->currencies = array();
	  	
	  	foreach ($currencies as $currency)
	  	{
	  		if ($currency->isDefault())
		    {
			  	$this->defaultCurrency = $currency;
			}
		
			$this->currencies[$currency->getID()] = $currency;
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
		$this->locale->translationManager()->setCacheFileDir(ClassLoader::getRealPath('storage.language'));
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
		else
		{
	  		$this->localeName = $this->getDefaultLanguageCode();
		}

		return $this->localeName;
	}

	private function loadConfig()
	{
	  	ClassLoader::import("application.model.system.Config");
		$this->config = Config::getInstance();
		return $this->config;
	}

	private function __get($name)
	{
		switch ($name)
	  	{
		    case 'locale':
		    	ClassLoader::import('library.locale.Locale');
		    	return $this->loadLocale();
		    break;

		    case 'localeName':
		    	ClassLoader::import('library.locale.Locale');
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