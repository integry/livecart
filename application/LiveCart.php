<?php

ClassLoader::import('framework.Application');

/**
 *  Implements LiveCart-specific application flow logic
 */
class LiveCart extends Application
{
	private static $pluginDirectories = array();

	private $isBackend = false;
	
  	/**
	 * Locale instance that application operates on
	 *
	 * @var Locale
	 */
	private $locale = null;

  	/**
	 * Configuration registry handler instance
	 *
	 * @var Config
	 */
	private $config = null;

  	/**
	 * Session handler instance
	 *
	 * @var Session
	 */
	private $session = null;

  	/**
	 * Current locale code (ex: lt, en, de)
	 *
	 * @var string
	 */
	private $localeName;

  	/**
	 * Default (base) language code/ID (ex: lt, en, de)
	 *
	 * @var string
	 */
	private $defaultLanguageID;
	
	private $requestLanguage;

	private $languageList = null;

	private $configFiles = array();

	private $currencies = null;

	private $defaultCurrency = null;
	
	private $defaultCurrencyCode = null;

	private $currencyArray;
	
	private $currencySet;
	
	const EXCLUDE_DEFAULT_CURRENCY = false;

	const INCLUDE_DEFAULT = true;	

	/**
	 * Returns an instance of LiveCart Application
	 *
	 * Method prevents of creating multiple application instances during one request
	 *
	 * @return LiveCart
	 */
	public function __construct()
	{
		parent::__construct();
		
		unset($this->session);
		unset($this->config);
		unset($this->locale);
		unset($this->localeName);
        		
        // LiveCart request routing rules
        include ClassLoader::getRealPath('application.configuration.route.backend') . '.php';        		
        		
		ClassLoader::import('application.model.ActiveRecordModel');
		ActiveRecordModel::setApplicationInstance($this);
		
		$compileDir = $this->isCustomizationMode() ? 'cache.templates_c.customize' : 'cache.templates_c';
		SmartyRenderer::setCompileDir(ClassLoader::getRealPath($compileDir));
	}
	
	/**
	 * Registers a new plugin directory (multiple plugin directories are supported)
	 *
	 * @param string $dir Full plugin directory path
	 */
	public static function registerPluginDirectory($dir)
	{
		self::$pluginDirectories[$dir] = true;
	}
	
	/**
	 * Gets view path for specified controllers action
	 *
	 * @param string $controllerName Controller name
	 * @param string $actionName Action name
	 * @return string View path
	 */
	public function getView($controllerName, $actionName)
	{		
		// get custom template path
        $path = ClassLoader::getRealPath('storage.customize.view.' . $controllerName . '.' . $actionName) . '.tpl';
        
        if (!is_readable($path))
        {
            return parent::getView($controllerName, $actionName);
        }
        
        return $path;
	}

	/**
	 * Gets a physical layout template path
	 *
	 * @param string $layout layout handle (filename without extension)
	 * @return string
	 */
	public function getLayoutPath($layout)
	{
		// get custom template path
        $path = ClassLoader::getRealPath('storage.customize.view.layout.' . $layout) . '.tpl';
        
        if (!is_readable($path))
        {
            return parent::getLayoutPath($layout);
        }
        
        return $path;
	}	
		
	/**
	 * Gets renderer for application
	 *
	 * @return Renderer
	 */
	public function getRenderer()
	{
		if (is_null($this->renderer))
		{
			ClassLoader::import('application.LiveCartRenderer');
			$this->renderer = new LiveCartRenderer($this);
		}
		
		$renderer = parent::getRenderer();

		if ($this->isCustomizationMode() && !$this->isBackend)
		{			
			$this->renderer->getSmartyInstance()->autoload_filters = array('pre' => array('templateLocator'));			
		}
		
		return $renderer;
	}	
		
	/**
	 * Gets specified controller instance
	 *
	 * @param string $controllerName Controller name
	 * @return Controller
	 * @throws ControllerNotFoundException if controller does not exist
	 */
	protected function getControllerInstance($controllerName)
	{
		if (substr($controllerName, 0, 8) == 'backend.')
		{
			$this->isBackend = true;
		}

		return parent::getControllerInstance($controllerName);
	}
		
	/**
	 * Executes controllers action and returns response
	 *
	 * @param string $controllerName Controller name
	 * @param string $actionName Action name
	 * @return Response
	 * @throws ApplicationException if error situation occurs
	 */
	protected function execute($controllerInstance, $actionName)
	{
		$response = parent::execute($controllerInstance, $actionName);
		
		$this->processPlugins($controllerInstance, $response);
	
		return $response;
	}    
	
	/**
 `	 * Execute response post-processor plugins        
 	 *
	 * @todo Cache plugin file locations
	 */
    private function processPlugins(Controller $controllerInstance, Response $response)
	{
        $name = $controllerInstance->getControllerName();
        $action = $controllerInstance->getRequest()->getActionName();
        
		ClassLoader::import('application.ControllerPlugin');
				
		$dirs = array_merge(array(ClassLoader::getRealPath('plugin.controller.' . $name . '.' . $action) => 0), self::$pluginDirectories);
				
        foreach ($dirs as $pluginDir => $type)
        {
			if ($type)
			{
				$pluginDir = $pluginDir . '/controller/' . $name . '/' . $action;
			}				

			if (!is_dir($pluginDir))
			{
	            continue;
	        }

			foreach (new DirectoryIterator($pluginDir) as $file)
			{
	            if (substr($file->getFileName(), -4) == '.php')
	            {
	                include_once($file->getPathname());
	                $class = substr($file->getFileName(), 0, -4);
	                $plugin = new $class($response, $controllerInstance);
	                $plugin->process();
	            }
	        }
		}
    }
    
	public function isCustomizationMode()
	{
		return $this->session->get('customizationMode');
	}

	public function isTranslationMode()
	{
		return $this->session->get('translationMode');
	}    
	
	private function loadSession()
	{
	  	ClassLoader::import("application.model.system.Config");
    	$this->session = new Session();
    	return $this->session;
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
	
    private function loadLocale()
	{
		ClassLoader::import('library.locale.Locale');
		    	
		$this->locale =	Locale::getInstance($this->localeName);
		$this->locale->translationManager()->setCacheFileDir(ClassLoader::getRealPath('storage.language'));
		$this->locale->translationManager()->setDefinitionFileDir(ClassLoader::getRealPath('application.configuration.language'));
		Locale::setCurrentLocale($this->localeName);

		$this->loadLanguageFiles();

		return $this->locale;
	}

	private function loadLocaleName()
	{
		ClassLoader::import('library.locale.Locale');
		    	
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

		    case 'session':
		    	return $this->loadSession();
		    break;

			default:
		    break;
		}
	}	
	
	



	/**
	 * @return Locale
	 */
	public function getLocale()
	{
	  	return $this->locale;
	}

	public function getConfig()
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

	public function getEnabledCountries()
	{
		$countries = $this->locale->info()->getAllCountries();
		$enabled = $this->config->get('ENABLED_COUNTRIES');
		
		return array_intersect_key($countries, $enabled);
	}

	public function isValidCountry($countryCode)
	{
		$enabled = $this->config->get('ENABLED_COUNTRIES');
		return isset($enabled[$countryCode]);		
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
		$handler = $this->config->get('CC_HANDLER');
		
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
			$value = $this->config->get($key);
			$key = substr($key, strlen($className) + 1);
			$inst->setConfigValue($key, $value);
		}

        return $inst;
    }

    public function getCardTypes(CreditCardPayment $handler)
    {
		$key = get_class($handler) . '_cardTypes';
		if ($this->config->isValueSet($key))
		{
			$types = array_keys($this->config->get($key));
			return array_combine($types, $types);			
		}
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
		return array_flip($this->config->get('SHIPPING_HANDLERS'));
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
			$value = $this->config->get($key);
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

	private function loadConfig()
	{
	  	ClassLoader::import("application.model.system.Config");
		$this->config = new Config($this);
		return $this->config;
	}

}

?>