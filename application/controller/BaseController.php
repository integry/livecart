<?php

ClassLoader::import("framework.request.Session");
ClassLoader::import("framework.controller.Controller");
ClassLoader::import("application.model.user.User");
ClassLoader::import("application.helper.*");
ClassLoader::import("application.model.system.Language");
ClassLoader::import("application.model.system.Store");
ClassLoader::import("library.locale.*");

/**
 * Base controller for the whole application
 * 
 * Store controller which implements common operations needed for both frontend and 
 * backend
 *
 * @package application.controller
 * @author Saulius Rupainis <saulius@integry.net>
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
	 * Store instance
	 *
	 * @var Store
	 */
	protected $store = null;
	
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
	 * Bese controller constructor: restores user object by using session data and 
	 * checks a permission to a requested action
	 *
	 * @param Request $request
	 * @throws AccessDeniedExeption
	 */
	public function __construct(Request $request)
	{
		parent::__construct($request);

		$this->session = new Session();
		$user = $this->session->getValue("user");
		if (!empty($user)) 
		{
			$this->user = unserialize($user);
		} 
		else 
		{
			$this->user = User::getInstanceByID(User::ANONYMOUS_USER_ID);
		}
		$this->router = Router::getInstance();
		if (!$this->user->hasAccess($this->getRoleName())) {
			//throw new AccessDeniedException($this->user, $this->request->getControllerName(), $this->request->getActionName());
		}

		$this->configFiles = $this->getConfigFiles();
		
		$this->store = Store::getInstance();
		$this->store->setRequestLanguage($this->request->getValue('requestLanguage'));				
		$this->store->setConfigFiles($this->configFiles);

		unset($this->locale);
		unset($this->config);
		
		// add language code to URL for non-default languages
		if ($this->store->getLocaleInstance()->getLocaleCode() != $this->store->getDefaultLanguageCode())
		{
			Router::setAutoAppendVariables(array('requestLanguage' => $this->store->getLocaleInstance()->getLocaleCode()));			
		}
//		print_r($this->user);
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
	
	protected function getSessionData($key = '')
	{
		return $this->store->getSession()->getControllerData($this, $key);
	}
	
	protected function setSessionData($key, $value)
	{
		return $this->store->getSession()->setControllerData($this, $key, $value);
	}
		
	/**
	 * Gets a @role tag value in a class and method comments
	 *
	 * @return string
	 * @todo default action and controller name should be defined in one place accessible by all framework parts
	 */
	private final function getRoleName()
	{
		$controllerClassName = get_class($this);
		$actionName = $this->request->getActionName();
		if (empty($actionName))
		{
			$actionName = "index";
		}

		$class = new ReflectionClass($controllerClassName);
		$classDocComment = $class->getDocComment();

		try
		{
			$method = new ReflectionMethod($controllerClassName, $actionName);
			$actionDocComment = $method->getDocComment();
		}
		catch (ReflectionException $e)
		{
			throw new ActionNotFoundException($controllerClassName, $actionName);
		}

		$roleTag = " @role";
		$classRoleMatches = array();
		$actionRoleMatches = array();
		preg_match("/".$roleTag." (.*)(\\r\\n|\\r|\\n)/U", $classDocComment, $classRoleMatches);
		preg_match("/".$roleTag." (.*)(\\r\\n|\\r|\\n)/U", $actionDocComment, $actionRoleMatches);

		$roleValue = "";

		if (!empty($classRoleMatches))
		{
			$roleValue = trim(substr($classRoleMatches[0], strlen($roleTag), strlen($classRoleMatches[0])));
		}
		if (!empty($actionRoleMatches))
		{
			$roleValue .= "." . trim(substr($actionRoleMatches[0], strlen($roleTag), strlen($actionRoleMatches[0])));
		}

		return $roleValue;
	}

	/**
	 * 	Automatically preloads language files
	 *
	 */
	private function getConfigFiles()
	{
		$controllerRoot = Classloader::getRealPath('application.controller');

		$files = array();

		// get all inherited controller classes
		$class = new ReflectionClass(get_class($this));
		while ($class->getParentClass())
		{
			$file = substr($class->getFileName(), strlen($controllerRoot) + 1);
			$files[] = substr($file, 0, -14);
			$class = $class->getParentClass();
		}

		return $files;
	}

	private function __get($name)
	{
		switch ($name)
	  	{
		    case 'locale':
		    	$this->locale = $this->store->getLocaleInstance();
				return $this->locale;
		    break;

		    case 'config':
		    	$this->config = $this->store->getConfigInstance();
				return $this->config;
		    break;

			default:
		    break;
		}
	}	
}

?>