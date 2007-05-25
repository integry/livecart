<?php

ClassLoader::import("framework.request.Session");
ClassLoader::import("framework.controller.exception.*");
ClassLoader::import("framework.controller.Controller");
ClassLoader::import("framework.roles.*");
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
	 * Roles
	 * 
	 * @var RolesParser
	 */
	protected $roles;

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

		$this->session = Session::getInstance();
		$this->user = User::getCurrentUser();
		$this->router = Router::getInstance();
	    
		// If backend controller is being used then we should 
	    // check for user permissions to use role assigned to current controller and action
		$rolesCacheDir = ClassLoader::getRealPath('cache.roles');
		if(!is_dir($rolesCacheDir))
		{
		    mkdir($rolesCacheDir);
		}
		
		$refl = new ReflectionClass($this);
        $controllerPath = $refl->getFileName();

		$cachePath = $rolesCacheDir . DIRECTORY_SEPARATOR . md5($controllerPath) . '.php';
		$this->roles = new RolesParser($controllerPath, $cachePath);
	    if($this->roles->wereExpired())
	    {
	        ClassLoader::import('application.model.role.Role');
	        Role::addNewRolesNames($this->roles->getRolesNames());
	    }
	    
	    $actionName = $this->request->getActionName();
	    $role = $this->roles->getRole($actionName);
	    $hasAccess = $this->user->hasAccess($role);
		
	    if (!$hasAccess) 
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
	}	
	
	/**
	 * @return RolesParser
	 */
	public function getRoles()
	{
	    return $this->roles;
	}
	
	/**
	 * Get logged user
	 *
	 * @return User
	 */
	public function getUser()
	{
	    return $this->user;
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
		return Session::getInstance()->getControllerData($this, $key);
	}
	
	protected function setSessionData($key, $value)
	{
		return Session::getInstance()->setControllerData($this, $key, $value);
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