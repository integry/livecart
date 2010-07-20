<?php

ClassLoader::import("framework.controller.exception.*");
ClassLoader::import("framework.controller.Controller");
ClassLoader::import("application.helper.*");
ClassLoader::import("application.model.system.Language");
ClassLoader::import("library.locale.*");
ClassLoader::import("library.locale.LCiTranslator");
ClassLoader::import("framework.request.validator.Form");
ClassLoader::import("framework.request.validator.RequestValidator");

/**
 * Base controller for the whole application
 *
 * Store controller which implements common operations needed for both frontend and
 * backend
 *
 * @package application.controller
 * @author Integry Systems
 */
abstract class BaseController extends Controller implements LCiTranslator
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
	public function __construct(LiveCart $application)
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

		$this->application->setRequestLanguage($this->request->get('requestLanguage'));
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

	public function getBlockResponse(&$block)
	{
		if ('getGenericBlock' == $block['call'][1])
		{
			$block['call'][0] = $this;
		}

		return parent::getBlockResponse($block);
	}

	public function getGenericBlock()
	{
		return new BlockResponse();
	}

	/**
	 * @return RolesParser
	 */
	public function getRoles()
	{
		return $this->roles;
	}

	protected function init()
	{
		parent::init();

		$this->application->processInitPlugins($this);
		$this->application->logStat('Init BaseController');
	}

	protected function setCache(OutputCache $cache)
	{
		$this->cacheHandler = $cache;
	}

	public function getCacheHandler()
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
	public function translate($key)
	{
		return $this->locale->translator()->translate($key);
	}

	/**
	 * Translate array recursively
	 *
	 * @see BaseController::translate
	 * @return array
	 */
	public function translateArray($array)
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
	public function makeText($key, $params)
	{
		return $this->locale->translator()->makeText($key, $params);
	}

	public function getUser()
	{
		if (empty($this->user))
		{
			ClassLoader::import('application.model.user.SessionUser');
			$sessionUser = new SessionUser();
			$this->user = $sessionUser->getUser();
		}

		return $this->user;
	}

	public function setUser(User $user)
	{
		$this->user = $user;
	}

	public function loadLanguageFile($langFile)
	{
		$this->application->loadLanguageFile($langFile);
	}

	public function getApplication()
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
		return $this->request->get('ajax');
	}

	protected function getRequestLanguage()
	{
		return $this->locale->getLocaleCode();
	}

	protected function setMessage($message)
	{
		if ($message)
		{
			$this->session->set('controllerMessage', $message);
		}
		else
		{
			$this->session->unsetValue('controllerMessage');
		}
	}

	public function getMessage()
	{
		$msg = $this->session->get('controllerMessage');
		$this->setMessage('');
		return $msg;
	}

	protected function getValidator($validatorName, Request $request = null)
	{
		$validator = new RequestValidator($validatorName, $request ? $request : $this->request);

		foreach ($this->application->getPlugins('validator/' . $validatorName) as $plugin)
		{
			if (!class_exists('ValidatorPlugin', false))
			{
				ClassLoader::import('application.ValidatorPlugin');
			}

			include_once $plugin['path'];
			$inst = new $plugin['class']($validator, $this->application);
			$inst->process();
		}

		return $validator;
	}

	protected function setErrorMessage($message)
	{
		if ($message)
		{
			$this->setSessionData('errorMessage', $message);
		}
		else
		{
			$this->session->unsetValue('errorMessage');
		}
	}

	public function getErrorMessage()
	{
		$msg = $this->getSessionData('errorMessage');
		$this->setErrorMessage('');
		return $msg;
	}

	/**
	 * 	Automatically preloads language files
	 *
	 */
	private function getConfigFiles()
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

	public function __get($name)
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
				ClassLoader::import("framework.request.Session");
				$this->session = new Session();
				return $this->session;
			break;

			default:
			break;
		}
	}

	public function recheckAccess(LiveCart $application)
	{
		$this->checkAccess();
	}

	/**
	 *  Permanent redirect for URLs changed with category/product names
	 */
	public function redirect301($oldHandle, $newHandle)
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

		ClassLoader::import("framework.roles.RolesDirectoryParser");
		ClassLoader::import("framework.roles.RolesParser");

		$this->roles = new RolesParser($controllerPath, $cachePath);
		if($this->roles->wereExpired())
		{
			ClassLoader::import('application.model.role.Role');
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
