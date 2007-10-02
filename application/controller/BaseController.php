<?php

ClassLoader::import("framework.controller.exception.*");
ClassLoader::import("framework.controller.Controller");
ClassLoader::import("application.helper.*");
ClassLoader::import("application.model.system.Language");
ClassLoader::import("library.locale.*");
ClassLoader::import("library.locale.LCiTranslator");

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
        
		unset($this->locale);
		unset($this->config);
		unset($this->user);
		unset($this->session);

		$this->router = $this->application->getRouter();

        if (!$application->isInstalled() && !($this instanceof InstallController))
        {
            header('Location: ' . $this->router->createUrl(array('controller' => 'install', 'action' => 'index')));
            exit;            
        }
	    
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
	
	/**
	 * @return RolesParser
	 */
	public function getRoles()
	{
	    return $this->roles;
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
	
	public function loadLanguageFile($langFile)
	{
		$this->configFiles[] = $langFile;
		$this->application->setConfigFiles($this->configFiles);
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
			if (substr($class->getFileName(), 0, strlen($controllerRoot)) == $controllerRoot)
			{
				$file = substr($class->getFileName(), strlen($controllerRoot) + 1);
			}
			else
			{
				$file = basename($class->getFileName());
			}			

			$files[] = substr($file, 0, -14);
			$class = $class->getParentClass();
		}

		$files[] = 'Custom';

		return $files;
	}

	protected function __get($name)
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
	
	private function checkAccess()
	{
		// If backend controller is being used then we should 
	    // check for user permissions to use role assigned to current controller and action
		$rolesCacheDir = ClassLoader::getRealPath('cache.roles');
		if(!is_dir($rolesCacheDir))
		{
		    mkdir($rolesCacheDir, 0777, true);
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