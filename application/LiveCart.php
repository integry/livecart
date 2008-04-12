<?php

ClassLoader::import('framework.Application');
ClassLoader::import('framework.response.ActionResponse');

/**
 *  Implements LiveCart-specific application flow logic
 *
 *  @package application
 *  @author Integry Systems
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

	/**
	 *  Determines if the application is running in development mode
	 *
	 *  The development mode has the following changes:
	 *	* SQL query logger is turned on (/cache/activerecord.log)
	 *	* JavaScript and CSS stylesheet files are unbundled (slower to download, but allows debugging)
	 *
	 *  The development mode can be turned on by creating a file or directory named "dev" in the /cache directory.
	 *  It can be turned off by simply deleting the "dev" file.
	 */
	private $isDevMode;

	/**
	 *  Determines if the application is installed
	 */
	private $isInstalled;

	/**
	 *  Active design theme
	 *	@see getTheme()
	 */
	private $theme = null;

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
		ClassLoader::import('application.model.ActiveRecordModel');
		ClassLoader::import('framework.renderer.SmartyRenderer');

		parent::__construct();

		unset($this->session, $this->config, $this->locale, $this->localeName);

		$dsnPath = ClassLoader::getRealPath("storage.configuration.database") . '.php';
		$this->isInstalled = file_exists($dsnPath);

		if ($this->isInstalled)
		{
			ActiveRecord::setDSN(include $dsnPath);
		}

		// LiveCart request routing rules
		include ClassLoader::getRealPath('application.configuration.route.backend') . '.php';

		ActiveRecordModel::setApplicationInstance($this);

		if (file_exists(ClassLoader::getRealPath("cache.dev")))
		{
			$this->setDevMode(true);
		}

		if ($this->isDevMode())
		{
			ActiveRecord::getLogger()->setLogFileName(ClassLoader::getRealPath("cache") . DIRECTORY_SEPARATOR . "activerecord.log");
			error_reporting(E_ALL);
			ini_set('display_errors', 'On');
		}

		$compileDir = $this->isCustomizationMode() ? 'cache.templates_c.customize' : 'cache.templates_c';
		SmartyRenderer::setCompileDir(ClassLoader::getRealPath($compileDir));

		// mod_rewrite disabled?
		if ($this->request->get('noRewrite'))
		{
			$this->router->setBaseDir($_SERVER['baseDir'], $_SERVER['virtualBaseDir']);
		}
	}

	public function setDevMode($devMode = true)
	{
		$this->isDevMode = $devMode;
	}

	public function isDevMode()
	{
		return $this->isDevMode;
	}

	public function isInstalled()
	{
		return $this->isInstalled;
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
		$controllerName = str_replace('.', DIRECTORY_SEPARATOR, $controllerName);

		if ($path = $this->getRenderer()->getTemplatePath($controllerName . '/' . $actionName . '.tpl'))
		{
			return $path;
		}
		else
		{
			return parent::getView($controllerName, $actionName);
		}
	}

	/**
	 * Gets a physical layout template path
	 *
	 * @param string $layout layout handle (filename without extension)
	 * @return string
	 */
	public function getLayoutPath($layout)
	{
		if ($path = $this->getRenderer()->getTemplatePath('layout/' . $layout . '.tpl'))
		{
			return $path;
		}
		else
		{
			return parent::getLayoutPath($layout);
		}
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
			$this->renderer->getSmartyInstance()->register_prefilter(array($this, 'templateLocator'));
		}

		return $renderer;
	}

	public function isBackend()
	{
		return $this->isBackend;
	}

	public function templateLocator($tplSource, $smarty)
	{
		$file = $smarty->_current_file;

		foreach (array_merge(array('custom:'), $this->getRenderer()->getTemplatePaths()) as $path)
		{
			if ($path == substr($file, 0, strlen($path)))
			{
				$file = substr($file, strlen($path));
			}
		}

		$file = str_replace('\\', '/', $file);

		$editUrl = $this->getRouter()->createUrl(array('controller' => 'backend.template', 'action' => 'editPopup', 'query' => array('file' => $file)), true);

		return '<div class="templateLocator"><span class="templateName"><a onclick="window.open(\'' . $editUrl . '\', \'template\', \'width=800,height=600,scrollbars=yes,resizable=yes\'); return false;" href="#">' . $file  . '</a></span>' . $tplSource . '</div>';
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
		$originalResponse = parent::execute($controllerInstance, $actionName);

		// override Apache's AddDefaultCharset directive
		if (get_class($originalResponse) ==  'ActionResponse')
		{
			$originalResponse->setHeader('Content-type', 'text/html;charset=utf-8');
		}

		$response = $this->processActionPlugins($controllerInstance, $originalResponse);

		if ($response !== $originalResponse)
		{
			$this->processResponse($response);
		}

		return $response;
	}

	protected function postProcessResponse(Response $response, Controller $controllerInstance)
	{
		if ($response instanceof BlockResponse)
		{
			$response->set('user', $controllerInstance->getUser()->toArray());
		}
	}

	/**
 `	 * Execute controller initialization plugins
	 */
	public function processInitPlugins(Controller $controllerInstance)
	{
		return $this->processPlugins($controllerInstance, new RawResponse, 'init');
	}

	/**
 `	 * Execute response post-processor plugins
	 */
	private function processActionPlugins(Controller $controllerInstance, Response $response)
	{
		return $this->processPlugins($controllerInstance, $response, $controllerInstance->getRequest()->getActionName());
	}

	private function processPlugins(Controller $controllerInstance, Response $response, $action)
	{
		ClassLoader::import('application.ControllerPlugin');

		$dirs = array_merge(array(ClassLoader::getRealPath('plugin') => 0), self::$pluginDirectories);

		$parent = get_class($controllerInstance);
		do
		{
			$hierarchy[strtolower(substr($parent, 0, -10))] = true;
			$parent = get_parent_class($parent);
		}
		while ($parent);

		$hierarchy[$controllerInstance->getControllerName()] = true;
		$hierarchy = array_keys($hierarchy);

		foreach ($dirs as $pluginRoot => $foo)
		{
			foreach ($hierarchy as $name)
			{
				$pluginDir = $pluginRoot . '/controller/' . $name . '/' . $action;

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

						$response = $plugin->getResponse();
						if ($plugin->isStopped())
						{
							return $response;
						}
					}
				}
			}
		}

		return $response;
	}

	/**
	 * Renders response from controller action
	 *
	 * @param string $controllerInstance Controller
	 * @param Response $response Response to render
	 * @return string Renderer content
	 * @throws ViewNotFoundException if view does not exists for specified controller
	 */
	protected function render(Controller $controllerInstance, Response $response, $actionName = null)
	{
		$output = parent::render($controllerInstance, $response, $actionName);

		if ($cache = $controllerInstance->getCacheHandler())
		{
			$cache->setData($output);
			$cache->save();
		}

		return $output;
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
	  	ClassLoader::import("framework.request.Session");
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
		$this->locale->translationManager()->setDefinitionFileDir(ClassLoader::getRealPath('storage.language'));
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

	/**
	 * Get config instance
	 *
	 * @return Config
	 */
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

			$langCache = ClassLoader::getRealPath('cache') . '/languages.php';

			if (file_exists($langCache))
			{
				$this->languageList = include $langCache;
			}
			else
			{
				try
				{
					$langFilter = new ARSelectFilter();
				  	$langFilter->setOrder(new ARFieldHandle("Language", "position"), ARSelectFilter::ORDER_ASC);
					$this->languageList = ActiveRecordModel::getRecordSet("Language", $langFilter);
					if (!$this->languageList->size())
					{
						throw new ApplicationException('No languages have been added');
					}
					file_put_contents($langCache, '<?php return unserialize(' . var_export(serialize($this->languageList), true) . '); ?>');
				}
				catch (Exception $e)
				{
					// if the database hasn't yet been created
					$this->languageList = new ARSet();
					$lang = ActiveRecordModel::getNewInstance('Language');
					$lang->setID('en');
					$lang->isEnabled->set(1);
					$lang->isDefault->set(1);
					$this->languageList->unshift($lang);
				}
			}
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

		$defLang = $this->getDefaultLanguageCode();

		foreach ($ret as $key => $data)
		{
			if ((($data['ID'] == $defLang) && !$includeDefaultLanguage) || (!$includeDisabledLanguages && $data['isEnabled'] == 0))
			{
				unset($ret[$key]);
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
			$def = $this->getDefaultCurrency();
			if ($def)
			{
				$this->defaultCurrencyCode = $def->getID();
			}
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
	 * Returns an array of available credit card handlers
	 */
	public function getExpressPaymentHandlerList($enabledOnly = false)
	{
		ClassLoader::import('library.payment.PaymentMethodManager');
		if (!$enabledOnly)
		{
			return PaymentMethodManager::getExpressPaymentHandlerList();
		}
		else
		{
			return is_array($this->config->get('EXPRESS_HANDLERS')) ? array_keys($this->config->get('EXPRESS_HANDLERS')) : array();
		}
	}

	public function getExpressPaymentHandler($handlerName, TransactionDetails $details = null)
	{
		if (!in_array($handlerName, $this->getExpressPaymentHandlerList(true)))
		{
			throw new Exception('Invalid express checkout handler');
		}

		ClassLoader::import('library.payment.method.express.' . $handlerName);

		return $this->getPaymentHandler($handlerName, $details);
	}

	/**
	 * Returns an instance of the selected credit card handler
	 */
	public function getCreditCardHandler(TransactionDetails $details = null)
	{
		$handler = $this->config->get('CC_HANDLER');

		ClassLoader::import('library.payment.method.cc.' . $handler);

		return $this->getPaymentHandler($handler, $details);
	}

	public function getPaymentHandler($className, TransactionDetails $details = null)
	{
		if (!class_exists($className, false))
		{
			ClassLoader::import('library.payment.method.' . $className);
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

		// check if the currency is supported by the payment handler
		$currency = $inst->getValidCurrency($details->currency->get());
		if (($details->currency->get() != $currency) && !is_null($details->currency->get()))
		{
			$newAmount = Currency::getInstanceById($currency, Currency::LOAD_DATA)->convertAmount(Currency::getInstanceById($details->currency->get(), Currency::LOAD_DATA), $details->amount->get());
			$details->currency->set($currency);
			$details->amount->set($newAmount);
		}

		return $inst;
	}

	/**
	 * Returns an array of available payment (non-credit card and non-express payment) handlers
	 */
	public function getPaymentHandlerList($enabledOnly = false)
	{
		ClassLoader::import('library.payment.PaymentMethodManager');
		if (!$enabledOnly)
		{
			return PaymentMethodManager::getRegularPaymentHandlerList();
		}
		else
		{
			return is_array($this->config->get('PAYMENT_HANDLERS')) ? array_keys($this->config->get('PAYMENT_HANDLERS')) : array();
		}
	}

	public function getCardTypes(CreditCardPayment $handler)
	{
		$key = get_class($handler) . '_cardTypes';
		if ($this->config->isValueSet($key, true))
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
		$handlers = $this->config->get('SHIPPING_HANDLERS');
		return is_array($handlers) ? array_flip($handlers) : array();
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

	public function getTheme()
	{
		if (is_null($this->theme))
		{
			$this->theme = $this->config->get('THEME');
			if ('barebone' == $this->theme)
			{
				$this->theme = '';
			}
		}

		return $this->theme;
	}

	public function setTheme($theme)
	{
		$this->theme = $theme;
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
