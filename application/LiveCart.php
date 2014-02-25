<?php
/*
*/

// experimental feature
define('ROUTE_CACHE', 0);

/**
 *  Implements LiveCart-specific application flow logic
 *
 *  @package application
 *  @author Integry Systems
 */
class LiveCart extends \Phalcon\Mvc\Application
{
	protected $configContainer;

	private $isBackend = false;

  	/**
	 * Locale instance that application operates on
	 *
	 * @var Locale
	 */
	private $locale = null;

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

	private $cache;

	private $plugins = null;

	private $sessionHandler;

	private $businessRuleController;

	const EXCLUDE_DEFAULT_CURRENCY = false;

	const INCLUDE_DEFAULT = true;

	/**
	 * Returns an instance of LiveCart Application
	 *
	 * Method prevents of creating multiple application instances during one request
	 *
	 * @return LiveCart
	 */
	public function __construct(\Phalcon\DI\FactoryDefault $di)
	{
		parent::__construct($di);

		unset($this->session, $this->locale, $this->localeName);

		$dsnPath = $this->config->getPath("storage/configuration/database") . '.php';
		$this->isInstalled = file_exists($dsnPath);

		if ($this->isInstalled)
		{
			//Set the database service
			$di->set('db', function() use ($dsnPath) {
				$dsn = parse_url(include $dsnPath);

				if (!empty($dsn['pass']))
				{
					$dsn['password'] = $dsn['pass'];
				}

				$connection = new MyPdoAdapter(array(
					"host" => $dsn['host'],
					"username" => $dsn['user'],
					"password" => !empty($dsn['password']) ? $dsn['password'] : '',
					"dbname" => substr($dsn['path'], 1)
				));
				
				//Listen all the database events
				$eventsManager = new \Phalcon\Events\Manager();
				$eventsManager->attach('db', function($event, $connection) {
					if ($event->getType() == 'beforeQuery') {
						$sql = $connection->getSQLStatement();
						foreach ((array)$connection->getSQLVariables() as $key => $value)
						{
							$sql = str_replace($key, $value, $sql);
						}
						
						if (!empty($GLOBALS['log'][$sql]))
						{
							//var_dump($connection->getSQLVariables());
							throw new Exception($sql);
						}
						
						$GLOBALS['log'][$sql] = true;
						
						//$logger->log($connection->getSQLStatement(), Logger::INFO);
					}
				});
				
				//$connection->setEventsManager($eventsManager);
				
				return $connection;
			});

			/*
			if (!session_id())
			{
				$session = new DatabaseSessionHandler();
				if ($this->getConfig()->get('USE_DEFAULT_SESSION_HANDLER') == false)
				{
					$session->setHandlerInstance();
				}
				$this->sessionHandler = $session;
			}
			*/
		}

		return;

		// LiveCart request routing rules
		$this->initRouter();

		if (file_exists($this->config->getPath('cache/dev')))
		{
			$this->setDevMode(true);
		}

		if ($this->isDevMode())
		{
			ActiveRecordModel::getLogger()->setLogFileName($this->config->getPath("cache") . DIRECTORY_SEPARATOR . "activerecord.log");
			error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
			ini_set('display_errors', 'On');
		}

		$compileDir = $this->isTemplateCustomizationMode() ? 'cache/templates/customize' : 'cache/templates';
		SmartyRenderer::setCompileDir($this->config->getPath($compileDir));

		// mod_rewrite disabled?
		if ($this->request->get('noRewrite'))
		{
			$this->router->setBaseDir($_SERVER['baseDir'], $_SERVER['virtualBaseDir']);
			//$this->router->enableURLRewrite(false);
		}
	}

	protected function loadCoreModules()
	{

	}

	public function run($redirect = false)
	{
		$this->processRuntimePlugins('startup');

		$res = parent::run($redirect);

		$this->processRuntimePlugins('shutdown');

		$cron = new Cron($this);
		if ($cron->isExecutable())
		{
			$cron->process();
		}
	}

	private function initRouter()
	{
		$routeCache = $this->getRouterCacheFile();

		if ($this->isInstalled)
		{
			// SSL
			if ($this->config->get('SSL_PAYMENT'))
			{
				$this->router->setSslAction('checkout', 'pay');
				$this->router->setSslAction('backend.payment', 'ccForm');
			}

			if ($this->config->get('SSL_CHECKOUT'))
			{
				$this->router->setSslAction('checkout');
				$this->router->setSslAction('onePageCheckout');
				$this->router->setSslAction('order', 'index');
				$this->router->setSslAction('order', 'multi');
				$this->router->setSslAction('order', 'update');
			}

			if ($this->config->get('SSL_CUSTOMER'))
			{
				$this->router->setSslAction('user');
			}

			if ($sslHost = $this->config->get('SSL_DOMAIN'))
			{
				if (!$this->router->isHttps())
				{
					session_start();
					$sslHost .= '?sid=' . session_id();
				}
				else
				{
					if ($this->request->get('sid'))
					{
						session_id($this->request->get('sid'));
					}
				}

				$this->router->setSslHost($sslHost);
			}
		}

		if (ROUTE_CACHE && file_exists($routeCache))
		{
			return;
		}

		foreach ($this->getConfigContainer()->getRouteFiles() as $file)
		{
			$routes = array();
			$ret = include $file;
			if (is_array($ret))
			{
				$routes = $ret;
			}

			foreach ($routes as $route)
			{
				$method = empty($route[3]) ? 'connect' : 'connectPriority';
				$this->router->$method($route[0], $route[1], $route[2]);
				$route[2]['requestLanguage'] = "[a-zA-Z]{2}";
				$this->router->$method(':requestLanguage/' . $route[0], $route[1], $route[2]);
			}
		}

		if (ROUTE_CACHE)
		{
			file_put_contents($routeCache, '<?php return unserialize(' . var_export(serialize($this->router), true) . '); ?>');
		}
	}

	public function setDevMode($devMode = true)
	{
		$this->isDevMode = $devMode;
	}

	public function setStatHandler(Stat $statHandler)
	{
		$this->statHandler = $statHandler;
	}

	public function logStat($step)
	{
		if (!empty($this->statHandler))
		{
			$this->statHandler->logStep($step);
		}
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

	public function isBackend()
	{
		return $this->isBackend;
	}

	public function templateLocator($tplSource, $smarty)
	{
		$file = $smarty->_current_file;

		if (substr($file, 0, 7) == 'custom:')
		{
			$file = substr($file, 7);
		}

		if (substr($file, 0, 1) == '@')
		{
			$file = $this->getRenderer()->getBaseTemplatePath($file);
		}

		if (!file_exists($file))
		{
			$file = $this->getRenderer()->getTemplatePath($file);
		}

/*
		$paths = array($this->config->getPath('storage/customize/view'),
					   $this->config->getPath('application/view'));

		foreach ($paths as $path)
		{
			if ($path == substr($file, 0, strlen($path)))
			{
				$file = substr($file, strlen($path) + 1);
				break;
			}
		}

		$file = str_replace('\\', '/', $file);
*/
		$file = $this->getRenderer()->getRelativeTemplatePath($file);

		$editUrl = $this->getRouter()->createUrl(array('controller' => 'backend.template', 'action' => 'editPopup', 'query' => array('file' => $file, 'theme' => '__theme__')), true);
		$editUrl = str_replace('__theme__', '{theme}', $editUrl);

		// @todo: temp fix. for some reason /public/ was added seemingly randomly for some templates at one store
		$editUrl = str_replace('/public/', '/', $editUrl);

		if ((strpos($tplSource, '{*nolive*}') === false) && (!strpos($file, 'frontend.tpl')))
		{
			return '<span class="templateLocator" ondblclick="window.open(\'' . $editUrl . '\', \'template\', \'width=800,height=600,scrollbars=yes,resizable=yes\'); Event.stop(event); return false;" onmouseover="this.addClassName(\'activeTpl\'); Event.stop(event);" onmouseout="this.removeClassName(\'activeTpl\'); Event.stop(event);"><span class="templateName"><a onclick="window.open(\'' .
		$editUrl . '\', \'template\', \'width=800,height=600,scrollbars=yes,resizable=yes\'); return false;" href="#">' . $file  . '</a></span>' . $tplSource . '</span>';
		}
		else
		{
			return $tplSource;
		}
	}

	/**
	 * Gets specified controller instance
	 *
	 * @param string $controllerName Controller name
	 * @return Controller
	 * @throws ControllerNotFoundException if controller does not exist
	 */
	public function getControllerInstance($controllerName)
	{
		if (substr($controllerName, 0, 8) == 'backend.')
		{
			$this->isBackend = true;
		}

		return parent::getControllerInstance($controllerName);
	}

	public function getControllerPath($controllerName)
	{
		if (empty($this->controllerDirectories[$controllerName]))
		{
			$this->getControllerInstance($controllerName);
		}

		if (!empty($this->controllerDirectories[$controllerName]))
		{
			return $this->controllerDirectories[$controllerName];
		}
	}

	protected function getControllerDirectories()
	{
		return $this->getConfigContainer()->getControllerDirectories();
	}

	protected function sendOutput($output)
	{
		if ($output)
		{
			if ($errorContent = ob_get_contents())
			{
				$output = $errorContent . $output;
			}

			$iniCompr = in_array(strtolower(ini_get('zlib.output_compression')), array('1', 'on'));
			if (!$errorContent && !$this->isDevMode() && function_exists('gzencode') && !empty($_SERVER['HTTP_ACCEPT_ENCODING']) && (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) && !headers_sent() && !$iniCompr)
			{
				$output = gzencode($output, 9);
				header('Content-Encoding: gzip');
			}

			if (!headers_sent() && !$this->isDevMode())
			{
				header('Content-Length: ' . strlen($output));
			}

			echo $output;
		}
	}

	/**
	 * Executes controllers action and returns response
	 *
	 * @param string $controllerName Controller name
	 * @param string $actionName Action name
	 * @return Response
	 * @throws ApplicationException if error situation occurs
	 */
	public function execute($controllerInstance, $actionName, $isBlock = false)
	{
		if ($this->isDevMode() && $isBlock && !empty($_REQUEST['noblock']))
		{
			return null;
		}

		if (!$isBlock)
		{
			$this->logStat('Before executing controller action');
		}
		else
		{
			$this->logStat('Before executing block: ' . $actionName);
		}

		if ($response = $this->processInitPlugins($controllerInstance, array('before-' . $actionName, 'before-init')))
		{
			if (!($response instanceof RawResponse) || $response->getContent())
			{
				$this->processResponse($response);
				return $response;
			}
		}

		if (!$isBlock)
		{
			$originalResponse = parent::execute($controllerInstance, $actionName);
			$this->logStat('Execute controller action');
		}
		else
		{
			$originalResponse = $controllerInstance->executeBlock($actionName);
			$this->logStat('Executed block: ' . $actionName);
			if (!$originalResponse)
			{
				return null;
			}
		}

		$response = $this->processActionPlugins($controllerInstance, $originalResponse, $actionName);

		if ($response !== $originalResponse)
		{
			$this->processResponse($response);
		}

		if (!$isBlock)
		{
			$this->logStat('Finish executing controller action (plugins, etc.)');
		}
		else
		{
			$this->logStat('Finished executing block plugins: ' . $actionName);
		}

		return $response;
	}

	protected function postProcessResponse(Response $response, Controller $controllerInstance)
	{
		if (!$response instanceof Renderable || !$this->isInstalled())
		{
			return false;
		}

		$response->set('user', $controllerInstance->getUser()->toArray());
		$response->set('message', $controllerInstance->getMessage());
		$response->set('errorMessage', $controllerInstance->getErrorMessage());
		if ($controllerInstance instanceof FrontendController)
		{
			$response->set('currency', $controllerInstance->getRequestCurrency());
		}

		// fetch queued EAV data
		if (class_exists('ActiveRecordModel', false))
		{
			ActiveRecordModel::loadEav();
		}

		$renderer = $this->getRenderer();

		if ($response instanceof ActionResponse && !$controllerInstance->isBlocksProcessed)
		{
			$controllerInstance->isBlocksProcessed = true;

			foreach ($renderer->getBlockConfiguration() as $object => $commands)
			{
				foreach ($commands as $command)
				{
					if ($renderer->isBlock($object))
					{
						$action = $command['action'];
						switch ($action['command'])
						{
							case 'replace':
								$action['command'] = 'append';
								$controllerInstance->removeBlock($object);

							case 'append':
							case 'prepend':
								if (!empty($action['isDefinedBlock']))
								{
									$action = array_merge($action, (array)array_shift($controllerInstance->getBlocks($action['view'])));
								}

								$controllerInstance->addBlock($object, $action['call'], $action['view'], $action['command'] == 'prepend');
								break;

							case 'remove':
								$controllerInstance->removeBlock($object);
								break;

							case 'theme':
								$this->setTheme($action['view']);
								break;
						}
					}
				}
			}
		}
	}

	/**
 `	 * Execute controller initialization plugins
	 */
	public function processInitPlugins(Controller $controllerInstance, $action = 'init')
	{
		return $this->processPlugins($controllerInstance, new RawResponse, $action);
	}

	private function getControllerHierarchy(ControllerBase $controllerInstance)
	{
		static $cache;

		$top = $parent = get_class($controllerInstance);

		if (!isset($cache[$top]))
		{
			do
			{
				if (substr($parent, 0, 7) !== 'Phalcon')
				{
					$name = $parent == 'ControllerBase' ? 'base' : strtolower(substr($parent, 0, -10));
					$hierarchy[$name] = true;
				}
				$parent = get_parent_class($parent);
			}
			while ($parent);

			// remove the last controller (identified by it's path instead)
			// array_shift($hierarchy);

			// $hierarchy[str_replace('.', '/', $controllerInstance->getControllerName())] = true;
			$hierarchy = array_keys($hierarchy);

			$cache[$top] = $hierarchy;
		}

		return $cache[$top];
	}

	public function processActionPlugins(ControllerBase $controllerInstance, $action)
	{
		if (!is_array($action))
		{
			$action = array($action);
		}

		foreach ($this->getControllerHierarchy($controllerInstance) as $name)
		{
			$plugins = array();
			foreach ($action as $act)
			{
				$plugins = array_merge($plugins, $this->getPlugins('controller/' . $name . '/' . $act));
			}

			$plugins = array_merge($plugins, $this->getPlugins('controller/' . $name . '/all'));

			foreach ($plugins as $plugin)
			{
				include_once($plugin['path']);
				$plugin = new $plugin['class']($controllerInstance, $this->getDI());
				$plugin->process();

				/*
				$response = $plugin->getResponse();
				if ($plugin->isStopped())
				{
					return $response;
				}
				*/
			}
		}

		//return $response;
	}

	public function processRuntimePlugins($path)
	{
		foreach($this->getPlugins($path) as $plugin)
		{
			include_once $plugin['path'];
			$inst = new $plugin['class']($this);
			$inst->process();
		}
	}

	public function processInstancePlugins($path, &$instance, $params = null)
	{
		foreach($this->getPlugins('instance/' . $path) as $plugin)
		{
			include_once $plugin['path'];
			$inst = new $plugin['class']($this, $instance, $params);

			$inst->process();
		}
	}

	public function getPlugins($path)
	{
		return $this->getConfigContainer()->getPlugins($path);
	}

	public function getPluginClasses($mountPath, $extension = 'php')
	{
		if (substr($mountPath, -1) != '/')
		{
			$mountPath .= '/';
		}

		$classes = array();
		foreach ($this->configContainer->getDirectoriesByMountPath($mountPath) as $dir)
		{
			foreach (glob($dir . '*.' . $extension) as $file)
			{
				$file = basename($file, '.' . $extension);
				$classes[] = $file;
			}
		}

		return $classes;
	}

	public function getPluginClassPath($mountPath, $class, $extension = 'php')
	{
		if (substr($mountPath, -1) != '/')
		{
			$mountPath .= '/';
		}

		foreach ($this->configContainer->getDirectoriesByMountPath($mountPath) as $dir)
		{
			$path = $dir . $class . '.' . $extension;
			if (file_exists($path))
			{
				return $path;
			}
		}
	}

	public function loadPluginClass($mountPath, $class)
	{
		if ($path = $this->getPluginClassPath($mountPath, $class))
		{
			include_once($path);
			return;
		}
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
		$this->logStat('Before page rendering');
		$output = parent::render($controllerInstance, $response, $actionName);

		if ($cache = $controllerInstance->getCacheHandler())
		{
			$cache->setData($output);
			$cache->save();
		}
		$this->logStat('Finished page rendering');
		return $output;
	}

	public function renderBlock($block, Controller $controllerInstance, $params = array())
	{
		$id = $block['view'];

		$cache = new OutputCache($id);
		$controllerInstance->setCache($cache);

		$this->processInstancePlugins('outputBlock', $block);

		if (is_string($block))
		{
			return $block;
		}

		$output = parent::renderBlock($block, $controllerInstance, $params);

		$params = array('block' => $block, 'output' => &$output);
		$this->processInstancePlugins('outputBlockAfter', $params);

		$this->logStat('Render ' . $block['container']);

		return $output;
	}

	public function isCustomizationMode()
	{
		return $this->session->get('customizationMode');
	}

	public function isTemplateCustomizationMode()
	{
		return $this->isCustomizationMode() && ('template' == $this->getCustomizationModeType());
	}

	public function isTranslationMode()
	{
		return $this->isCustomizationMode() && ('translate' == $this->getCustomizationModeType());
	}

	public function getCustomizationModeType()
	{
		return $this->session->get('customizationModeType');
	}

	public function getSessionHandler()
	{
		return $this->sessionHandler;
	}

	public function getSession()
	{
		return $this->loadSession();
	}

	private function loadSession()
	{

		// avoid unserialize failures (3rd party application instances, etc.)
		ClassLoader::ignoreMissingClasses(true);

		$this->session = new Session();

		//ClassLoader::ignoreMissingClasses(false);

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
		if (empty($this->locale))
		{
			$this->locale =	\locale\Locale::getInstance($this->localeName, $this->getDI());
			$this->locale->translationManager()->setCacheFileDir($this->config->getPath('storage/language'));

			foreach ($this->getConfigContainer()->getLanguageDirectories() as $dir)
			{
				$this->locale->translationManager()->setDefinitionFileDir($dir);
			}

			$this->locale->translationManager()->setDefinitionFileDir($this->config->getPath('storage/language'));
			\locale\Locale::setCurrentLocale($this->localeName);

			$this->loadLanguageFiles();
		}

		return $this->locale;
	}

	private function loadLocaleName()
	{
		if (empty($this->localeName))
		{
			if ($this->requestLanguage)
			{
				$this->localeName = $this->requestLanguage;
			}
			else
			{
				$this->localeName = $this->getDefaultLanguageCode();
			}
		}

		return $this->localeName;
	}

	private function getRouterCacheFile()
	{
		static $cacheFile;

		if (!$cacheFile)
		{
			$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
			$cacheFile = $this->config->getPath('cache/') . 'router-' . $host . '.php';
		}

		return $cacheFile;
	}

	public function __get($name)
	{
		switch ($name)
	  	{
			case 'locale':
				return $this->loadLocale();
			break;

			case 'localeName':
				return $this->loadLocaleName();
			break;

			case 'session':
				return $this->loadSession();
			break;

			default:
				return parent::__get($name);
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

	public function setLocale(\locale\Locale $locale)
	{
	  	$this->locale = $locale;
	  	$this->localeName = $locale->getLocaleCode();
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
			$this->languageList = \system\Language::getLanguageList($this->getDI());
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
		$ret = $this->getLanguageList()->toArray();

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
				(($lang->isEnabled == 1) || $includeInactiveLanguages))
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
		if (empty($this->defaultLanguageCode))
		{
			$langList = $this->getLanguageList();
			foreach ($langList as $lang)
			{
				if ($lang->isDefault())
				{
					$this->defaultLanguageCode = $lang->ID;
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
	  	if ($langCode)
	  	{
			$this->requestLanguage = $langCode;
			unset($this->locale, $this->localeName);
		}
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

	public function getDisplayTaxPrice($price, $product)
	{
		if (!$product)
		{
			return $price;
		}

		if (!$this->config->get('INCLUDE_BASE_TAXES'))
		{
						$price = OrderedItem::reduceBaseTaxes($price, $product);
		}
		/*
		else
		{
			$price = $price * 1.25;
		}
		*/

		return $price;
	}

	/**
	 * Returns an array of available credit card handlers
	 */
	public function getCreditCardHandlerList()
	{
				return PaymentMethodManager::getCreditCardHandlerList();
	}

	/**
	 * Returns an array of available credit card handlers
	 */
	public function getExpressPaymentHandlerList($enabledOnly = false)
	{
				if (!$enabledOnly)
		{
			return PaymentMethodManager::getExpressPaymentHandlerList();
		}
		else
		{
			return is_array($this->config->get('EXPRESS_HANDLERS')) ? array_keys($this->config->get('EXPRESS_HANDLERS')) : array();
		}
	}

	public function getExpressPaymentHandler($handlerName, LiveCartTransaction $details = null)
	{
		if (!in_array($handlerName, $this->getExpressPaymentHandlerList(true)))
		{
			throw new Exception('Invalid express checkout handler');
		}

		ClassLoader::importNow('library/payment/method/express.' . $handlerName);

		return $this->getPaymentHandler($handlerName, $details);
	}

	/**
	 * Returns an instance of the selected credit card handler
	 */
	public function getCreditCardHandler(LiveCartTransaction $details = null)
	{
		$handler = $this->config->get('CC_HANDLER');

		ClassLoader::importNow('library/payment/method/cc.' . $handler);

		return $this->getPaymentHandler($handler, $details);
	}

	public function getPaymentHandler($className, LiveCartTransaction $details = null)
	{
		if (!class_exists($className, false))
		{
				if ('OfflineTransactionHandler' == $className)
				{
										}
				else
				{
					ClassLoader::importNow('library/payment/method.*');
					ClassLoader::importNow('library/payment/method/cc.*');
					ClassLoader::importNow('library/payment/method/express.*');
				}
		}

		if (is_null($details))
		{
			$details = new TransactionDetails();
		}

		$inst = new $className($details);

		if ($details instanceof LiveCartTransaction)
		{
			$inst->orderBy($details->getorderBy());
		}

		$c = $this->config->getSection('payment/' . $className);
		foreach ($c as $key => $value)
		{
			$value = $this->config->get($key);
			$key = substr($key, strlen($className) + 1);
			$inst->setConfigValue($key, $value);
		}

		// check if the currency is supported by the payment handler
		$currency = $inst->getValidCurrency($details->currency);
		if (($details->currency != $currency) && !is_null($details->currency))
		{
			$newAmount = Currency::getInstanceById($currency, Currency::LOAD_DATA)->convertAmount(Currency::getInstanceById($details->currency, Currency::LOAD_DATA), $details->amount);
			$details->currency->set($currency);
			$details->amount->set(round($newAmount, 2));
		}

		$inst->setApplication($this);

		return $inst;
	}

	/**
	 * Returns an array of available payment (non-credit card and non-express payment) handlers
	 */
	public function getPaymentHandlerList($enabledOnly = false)
	{
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
		$key = get_class($handler) . '_customCardTypes';
		if ($this->config->has($key))
		{
			if ($types = trim($this->config->get($key)))
			{
				$types = explode(',', $types);
				foreach ($types as $key => $type)
				{
					$types[$key] = trim($type);
				}

				return array_combine($types, $types);
			}
		}

		$key = get_class($handler) . '_cardTypes';
		if ($this->config->has($key))
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
				return ShippingMethodManager::getHandlerList();
	}

	/**
	 * Returns an array of enabled real-time shipping rate services
	 */
	public function getEnabledRealTimeShippingServices()
	{
		$handlers = $this->config->get('SHIPPING_HANDLERS');
		return is_array($handlers) ? array_keys($handlers) : array();
	}

	/**
	 * Returns a shipping handler instance
	 */
	public function getShippingHandler($className)
	{

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

	public function isInventoryTracking()
	{
		return $this->config->get('INVENTORY_TRACKING') != 'DISABLE';
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
		$this->getRenderer()->resetPaths();
	}

	public function getBusinessRuleController()
	{
		if (!$this->businessRuleController)
		{
			$context = new BusinessRuleContext();

			if ($items = SessionOrder::getOrderItems())
			{
				$context->orderBy($items);
			}

			if ($this->sessionUser->getUser())
			{
				$context->setUser($this->sessionUser->getUser());
			}

			$this->businessRuleController = new BusinessRuleController($context);

			if ($this->isBackend())
			{
				$this->businessRuleController->disableDisplayDiscounts();
			}
		}

		return $this->businessRuleController;
	}

	public function getFullUploadUrl($relativePath)
	{
		if ($customDomain = $this->config->get('IMAGE_DOMAIN'))
		{
			$url = 'http://' . $customDomain . str_replace('/public/upload', '', $relativePath);
		}
		else
		{
			$url = $this->router->createFullUrl($relativePath, null, true);
		}

		return $url;
	}

	public function getPublicUrl($relativePath)
	{
		if ($customDomain = $this->config->get('STATIC_DOMAIN'))
		{
			$url = 'http://' . $customDomain . '/' . str_replace('/public/', '', $relativePath);
		}
		else
		{
			$url = $this->router->createFullUrl($relativePath, null, true);
		}

		return $url;
	}

	public function clearCachedVars()
	{
		$this->defaultLanguageID = null;
		$this->requestLanguage = null;
		$this->languageList = null;
		$this->configFiles = array();
		$this->currencies = null;
		$this->defaultCurrency = null;
		$this->defaultCurrencyCode = null;
		$this->currencyArray = null;
		$this->currencySet = null;
	}

	/**
	 * Loads currency data from database
	 */
	private function loadCurrencyData()
	{
		$useCache = false;
		$cache = Currency::getCacheFile();

		if (file_exists($cache) && $useCache)
		{
			$this->currencies = include $cache;
		}
		else
		{
			foreach (Currency::query()->where('isEnabled = 1')->orderBy('position ASC')->execute() as $currency)
			{
				$this->currencies[$currency->getID()] = $currency;
			}

			if ($useCache)
			{
				file_put_contents($cache, '<?php return unserialize(' . var_export(serialize($this->currencies), true) . '); ?>');
			}
		}

		foreach ($this->currencies as $currency)
		{
			if ($currency->isDefault())
			{
				$this->defaultCurrency = $currency;
			}
		}
	}

	public function loadLanguageFile($langFile)
	{
		$this->locale->translationManager()->loadFile($langFile);
		$this->configFiles[] = $langFile;
	}

	public function loadLanguageFiles()
	{
		foreach ($this->configFiles as $file)
		{
			$this->locale->translationManager()->loadFile($file);
		}
	}

	public function getCache()
	{
		if (!$this->cache)
		{
			$class = $this->config->get('CACHE_METHOD');

			if ($class)
			{
								$this->cache = new $class($this);
			}

			// default to file cache
			if (!$class || !$this->cache->isValid())
			{
								$this->cache = new FileCache($this);
			}
		}

		return $this->cache;
	}

	public function getCron()
	{
		if (!$this->cron)
		{

			$this->cron = new Cron($this);
		}

		return $this->cron;
	}

	public function getConfigContainer()
	{
		if (!$this->configContainer)
		{
			$path = $this->config->getPath('cache/configurationContainer') . '.php';
			if (file_exists($path))
			{
				$this->configContainer = include $path;
				$this->configContainer->setDI($this->getDI());
			}
			else
			{
				$this->configContainer = new ConfigurationContainer('/', $this->getDI());
				$this->configContainer->getModules();
				$this->configContainer->getChildPlugins();
				$serialized = serialize($this->configContainer);

				if (!@file_put_contents($path, '<?php return unserialize(' . var_export($serialized, true) . '); ?>'))
				{
					ini_set('display_errors', 'Off');
				}
			}
		}

		return $this->configContainer;
	}

	public function registerModule($module)
	{
		$this->getConfigContainer()->addModule($module);
		$this->plugins = null;
	}

	public function getModules()
	{

	}

	public function serialize()
	{
		return null;
	}

	public function unserialize($serializedData)
	{
		return null;
	}

	public function rmdir_recurse($path)
	{
		$path= rtrim($path, '/').'/';

		if (!file_exists($path))
		{
			return;
		}

		$handle = opendir($path);
		for (;false !== ($file = readdir($handle));)
			if($file != "." and $file != ".." ) {
				$fullpath= $path.$file;
				if( is_dir($fullpath) ) {
					$this->rmdir_recurse($fullpath);
				} else {
					unlink($fullpath);
				}
		}
		closedir($handle);
		rmdir($path);
	}
}

?>
