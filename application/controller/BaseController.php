<?php

/**
 * Base controller for the whole application
 *
 * Store controller which implements common operations needed for both frontend and
 * backend
 *
 * @package application.controller
 * @author Integry Systems
 */
abstract class ControllerBase extends \Phalcon\Mvc\Controller // implements LCiTranslator
{
	/**
	 * System user
	 *
	 * @var User
	 */
	protected $user = null;

	/**
	 * Session instance
	 *
	 * @var Session
	 */
	protected $session = null;

	/**
	 * Router instance
	 *
	 * @var Router
	 */
	protected $router = null;

	/**
	 * Locale
	 *
	 * @var Locale
	 */
	protected $locale = null;

	/**
	 * Configuration handler instance
	 *
	 * @var Config
	 */
	protected $config = null;

	/**
	 * Configuration files (language, registry)
	 */
	protected $configFiles = array();

	/**
	 * Roles
	 *
	 * @var RolesParser
	 */
	protected $roles;

	protected $cacheHandler;

	/**
	 * Bese controller constructor: restores user object by using session data and
	 * checks a permission to a requested action
	 *
	 * @param LiveCart $application Application instance
	 * @throws AccessDeniedExeption
	 */
	public function __constructAction(LiveCart $application)
	{
		parent::__construct($application);

		$this->router = $this->application->getRouter();

		if (!$application->isInstalled() && !($this instanceof InstallController))
		{
			header('Location: ' . $this->router->createUrl(array('controller' => 'install', 'action' => 'index')));
			exit;
		}

		unset($this->locale);
		unset($this->config);
		unset($this->user);
		unset($this->session);

		$this->checkAccess();

		$this->application->setRequestLanguage($this->request->gget('requestLanguage'));
		$this->configFiles = $this->getConfigFiles();
		$this->application->setConfigFiles($this->configFiles);

		$localeCode = $this->application->getLocaleCode();

		// add language code to URL for non-default languages
		if ($localeCode != $this->application->getDefaultLanguageCode())
		{
			$this->router->setAutoAppendVariables(array('requestLanguage' => $localeCode));
		}

		// verify that the action is accessed via HTTPS if it is required
		if ($this->router->isSSL($this->request->getControllerName(), $this->request->getActionName()) && !$this->router->isHttps())
		{
			header('Location: ' . $this->router->createFullUrl($_SERVER['REQUEST_URI'], true));
			exit;
		}
	}

	public function getBlockResponseAction(&$block)
	{
		if ('getGenericBlock' == $block['call'][1])
		{
			$block['call'][0] = $this;
		}

		return parent::getBlockResponse($block);
	}

	public function getGenericBlockAction()
	{
		return new BlockResponse();
	}

	/**
	 * @return RolesParser
	 */
	public function getRolesAction()
	{
		return $this->roles;
	}

	protected function init()
	{
		parent::initialize();

		$pluginRes = $this->application->processInitPlugins($this);
		$this->application->logStat('Init BaseController');

		if ((!$pluginRes instanceof RawResponse) || $pluginRes->getContent())
		{
			return $pluginRes;
		}
	}

	public function allowCacheAction()
	{
		$this->getCacheHandler()->enable();
	}

	public function setCacheAction(OutputCache $cache)
	{
		$this->cacheHandler = $cache;
	}

	public function getCacheHandlerAction()
	{
		return $this->cacheHandler;
	}

	/**
	 * Translate text passed by reference.
	 *
	 * @see BaseController::translate
	 * @see BaseController::translateArray
	 */
	private function translateByReference(&$text)
	{
		$text = $this->translate($text);
	}

	/**
	 * Translates text using Locale::LCInterfaceTranslator
	 * @param string $key
	 * @return string
	 */
	public function translateAction($key)
	{
		return $this->locale->translator()->translate($key);
	}

	/**
	 * Translate array recursively
	 *
	 * @see BaseController::translate
	 * @return array
	 */
	public function translateArrayAction($array)
	{
		array_walk_recursive($array, array(&$this, 'translateByReference'));

		return $array;
	}

	/**
	 * Performs MakeText translation using Locale::LCInterfaceTranslator
	 * @param string $key
	 * @param array $params
	 * @return string
	 */
	public function makeTextAction($key, $params)
	{
		return $this->locale->translator()->makeText($key, $params);
	}

	public function getUserAction()
	{
		if (empty($this->user))
		{
						$sessionUser = new SessionUser();
			$this->user = $sessionUser->getUser();
		}

		return $this->user;
	}

	public function setUserAction(User $user)
	{
		$this->user = $user;
	}

	public function loadLanguageFileAction($langFile)
	{
		$this->application->loadLanguageFile($langFile);
	}

	public function getApplicationAction()
	{
		return $this->application;
	}

	protected function getSessionData($key = '')
	{
		return $this->session->getControllerData($this, $key);
	}

	protected function setSessionData($key, $value)
	{
		return $this->session->setControllerData($this, $key, $value);
	}

	protected function isAjax()
	{
		return $this->request->gget('ajax');
	}

	protected function getRequestLanguage()
	{
		return $this->locale->getLocaleCode();
	}

	protected function setMessage($message)
	{
		$this->session->set('controllerMessage', $message);
	}

	public function getMessageAction()
	{
		$msg = $this->session->get('controllerMessage');
		$this->setMessage('');
		return $msg;
	}

	protected function getValidator($validatorName, Request $request = null)
	{
		$validator = new LiveCartValidator($validatorName, $request ? $request : $this->request);
		$validator->setApplication($this->application);

		return $validator;
	}

	protected function setErrorMessage($message)
	{
		$this->setSessionData('errorMessage', $message);
	}

	public function getErrorMessageAction()
	{
		$msg = $this->getSessionData('errorMessage');
		$this->setErrorMessage('');
		return $msg;
	}

	/**
	 * 	Automatically preloads language files
	 *
	 */
	public function getConfigFilesAction()
	{
		$controllerRoot = $this->application->getConfigContainer()->getControllerDirectories();

		$files = array();

		// get all inherited controller classes
		$class = new ReflectionClass(get_class($this));
		while ($class->getParentClass())
		{
			$fileName = $class->getFileName();
			$controllerDir = null;

			foreach ($controllerRoot as $dir)
			{
				if (substr($fileName, 0, strlen($dir)) == $dir)
				{
					$controllerDir = $dir;
					break;
				}
			}

			if ($controllerDir)
			{
				$file = substr($fileName, strlen($controllerDir) + 1);
			}
			else
			{
				$file = basename($fileName);
			}

			$files[] = substr($file, 0, -14);
			$class = $class->getParentClass();
		}

		$files = array_reverse($files);
		$files[] = 'Custom';

		return $files;
	}

	public function invalidateCacheOnUpdateAction($class)
	{
		//return $this->getCache()->invalidateCacheOnUpdate($class);
	}

	public function setCacheVarAction($param, $value)
	{
		//return $this->getCache()->setCacheVar($param, $value);
	}

	public function setRequestVarAction($key)
	{

	}

	public function __getAction($name)
	{
		switch ($name)
	  	{
			case 'locale':
				$this->locale = $this->application->getLocale();
				return $this->locale;
			break;

			case 'config':
				$this->config = $this->application->getConfig();
				return $this->config;
			break;

			case 'user':
				return $this->getUser();
			break;

			case 'session':
								$this->session = new Session();
				return $this->session;
			break;

			default:
			break;
		}
	}

	public function recheckAccessAction(LiveCart $application)
	{
		$this->checkAccess();
	}

	/**
	 *  Permanent redirect for URLs changed with category/product names
	 */
	public function redirect301Action($oldHandle, $newHandle)
	{
		$oldHandle = urlencode($oldHandle);
		$newHandle = urlencode($newHandle);
		if (($oldHandle != $newHandle) && $this->config->get('URL_301_AUTO_REDIRECT'))
		{
			$oldUri = $_SERVER['REQUEST_URI'];
			$newUri = str_replace($oldHandle, $newHandle, $oldUri);
			if ($newUri != $oldUri)
			{
				header('HTTP/1.1 301 Moved Permanently');
				header('Location: ' . $newUri);
				exit;
			}
		}
	}

	protected function checkAccess()
	{
		// If backend controller is being used then we should
		// check for user permissions to use role assigned to current controller and action
		$rolesCacheDir = ClassLoader::getRealPath('cache.roles');
		if(!is_dir($rolesCacheDir))
		{
			if (!@mkdir($rolesCacheDir, 0777, true))
			{
				return false;
			}
		}

		$refl = new ReflectionClass($this);
		$controllerPath = $refl->getFileName();

		$cachePath = $rolesCacheDir . DIRECTORY_SEPARATOR . md5($controllerPath) . '.php';


		$this->roles = new RolesParser($controllerPath, $cachePath);
		if($this->roles->wereExpired())
		{
						Role::addNewRolesNames($this->roles->getRolesNames());
		}

		$role = $this->roles->getRole($this->request->getActionName());

		if ($role)
		{
			if (!$this->user->hasAccess($role))
			{
				if($this->user->isAnonymous())
				{
					throw new UnauthorizedException($this);
				}
				else
				{
					throw new ForbiddenException($this);
				}
			}
		}
	}
}

?>
