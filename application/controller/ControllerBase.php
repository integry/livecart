<?php

/**
 * Base controller for the whole application
 *
 * Store controller which implements common operations needed for both frontend and
 * backend
 *
 * @package application/controller
 * @author Integry Systems
 */
abstract class ControllerBase extends \Phalcon\Mvc\Controller // implements LCiTranslator
{
	/**
	 * Locale
	 *
	 * @var Locale
	 */
	protected $locale = null;

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
	 * Base controller constructor: restores user object by using session data and
	 * checks a permission to a requested action
	 *
	 * @throws AccessDeniedExeption
	 */
	public function initialize()
	{
		if (!$this->application->isInstalled() && !($this instanceof InstallController))
		{
			header('Location: ' . $this->url->get('install/index'));
			exit;
		}

		unset($this->locale);

		$this->checkAccess();

		$this->application->setRequestLanguage($this->request->get('requestLanguage'));
		$this->configFiles = $this->getConfigFiles();
		$this->application->setConfigFiles($this->configFiles);

		/*
		$localeCode = $this->application->getLocaleCode();

		// add language code to URL for non-default languages
		if ($localeCode != $this->application->getDefaultLanguageCode())
		{
			$this->router->setAutoAppendVariables(array('requestLanguage' => $localeCode));
		}

		// verify that the action is accessed via HTTPS if it is required
		if ($this->router->isSSL($this->request->getControllerName(), $this->router->getActionName()) && !$this->router->isHttps())
		{
			header('Location: ' . $this->router->createFullUrl($_SERVER['REQUEST_URI'], true));
			exit;
		}
		*/
	}

	public function set($key, $value)
	{
		$this->view->$key = $value;
	}

	public function get($key)
	{
		return $this->view->$key;
	}

	public function setValidator($validator)
	{
		$validator->processPlugins();
		$this->set('validator', $validator);

		$bag = new \Phalcon\Session\Bag('errors/' . $this->router->getControllerName() . '/' . $this->router->getActionName());
		if ($bag->has('errors'))
		{
			foreach ($bag->get('errors') as $error)
			{
				$validator->appendMessage($error);
			}
			$validator->setRestoredValues($bag->get('request'));
		}
		$bag->destroy();
	}

	public function setErrors($errors, $action)
	{
		$bag = new \Phalcon\Session\Bag('errors/' . $action);
		$bag->errors = $errors;
		$bag->request = $_REQUEST;
	}

	public function returnErrors($action, LiveCartValidator $validator)
	{
		$this->setErrors($validator->getMessages(), $action);
		return $this->response->redirect($action);
	}

	protected function getModelErrors($modelMessages)
	{
		$errors = array();
		foreach ($modelMessages as $message)
		{
			$errors[$message->getField()][] = $message->getMessage();
		}

		return $errors;
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
		parent::initialize();

		$pluginRes = $this->application->processInitPlugins($this);
		$this->application->logStat('Init BaseController');

		if ((!$pluginRes instanceof RawResponse) || $pluginRes->getContent())
		{
			return $pluginRes;
		}
	}

	public function allowCache()
	{
		$this->getCacheHandler()->enable();
	}

	public function setCache(OutputCache $cache)
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
		$this->session->set('controllerMessage', $message);
	}

	public function getMessage()
	{
		$msg = $this->session->get('controllerMessage');
		$this->setMessage('');
		return $msg;
	}

	protected function getValidator($validatorName, \Phalcon\Http\Request $request = null)
	{
		$validator = new LiveCartValidator();
		$validator->setName($validatorName);
		$validator->setDI($this->getDI());

		return $validator;
	}

	protected function setErrorMessage($message)
	{
		$this->setSessionData('errorMessage', $message);
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
	public function getConfigFiles()
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

	public function invalidateCacheOnUpdate($class)
	{
		//return $this->getCache()->invalidateCacheOnUpdate($class);
	}

	public function setCacheVar($param, $value)
	{
		//return $this->getCache()->setCacheVar($param, $value);
	}

	public function __get($name)
	{
		switch ($name)
	  	{
			case 'locale':
				$this->locale = $this->application->getLocale();
				return $this->locale;
			break;

			default:
				return parent::__get($name);
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
		$rolesCacheDir = $this->config->getPath('cache/roles');
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

		$this->roles = new \role\RolesParser($controllerPath, $cachePath);
		if($this->roles->wereExpired())
		{
			\role\Role::addNewRolesNames($this->roles->getRolesNames());
		}

		$role = $this->roles->getRole($this->router->getActionName());
		if ($role)
		{
			if (!$this->user->hasAccess($role))
			{
				if ($this->user->isAnonymous())
				{
					throw new UnauthorizedException();
				}
				else
				{
					throw new ForbiddenException($this);
				}
			}
		}
	}

	public function afterExecuteRoute(\Phalcon\Mvc\Dispatcher $dispatcher)
	{
		$this->application->processActionPlugins($this, $dispatcher->getActionName());
	}

	public function di($set)
	{
		$a = $this->di;
		return $a($set);
	}
}

?>
