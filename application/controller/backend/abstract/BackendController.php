<?php

ClassLoader::import("application.controller.BaseController");
ClassLoader::import("application.helper.*");
ClassLoader::import("application.model.system.Language");
ClassLoader::import("application.model.system.Store");
ClassLoader::import("library.locale.*");
ClassLoader::import("library.json.json");

/**
 * Generic backend controller for administrative tools (actions, modules etc.)
 *
 * @package application.backend.controller.abstract
 */
abstract class BackendController extends BaseController implements LCiTranslator 
{	
	/**
	 * Locale instance that application operates on
	 *
	 * @var Locale
	 */
	protected $locale = null;
	protected $localeName;
	protected $rootDirectory = "";
	
	/**
	 * Store instance
	 *
	 * @var Store
	 */
	protected $store = null;
	
	public function __construct(Request $request) 
	{
		parent::__construct($request);
		
		// unset locale variables to make use of lazy loading
		unset($this->locale);
		unset($this->localeName);
									
		if (!$this->user->hasAccess($this->getRoleName())) {	
			//throw new AccessDeniedException($this->user, $this->request->getControllerName(), $this->request->getActionName());
		}
		
		$this->store = Store::getInstance();
		
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

	public function init()
	{
		$this->addBlock('langMenu', 'langMenu');	
		Application::getInstance()->getRenderer()->setValue('BASE_URL', Router::getBaseUrl());
	}
	

	protected function langMenuBlock() 
	{
		$response =	new BlockResponse();
	  	$router = Router::getInstance();
	  	$response->setValue('returnRoute', base64_encode($router->getRequestedRoute()));
		return $response;	  	
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
	private function loadLanguageFiles()
	{
		// get all inherited controller classes
		$class = get_class($this);
		$classes = array();
		$lastClass = "";
		
		while ($class != $lastClass)
		{
		  	$lastClass = $class;
		 	$classes[] = $class;
		 	$class = get_parent_class($class);
		}
		
		// get class file paths (to be mapped with language file paths) and load language files
		$included = array();
		$controllerRoot = Classloader::getRealPath('application.controller');

		foreach (array_reverse(get_included_files()) as $file)
		{
			$class = basename($file,'.php');
			if (class_exists($class, false) && is_subclass_of($class, 'Controller'))
			{
				$file = substr($file, strlen($controllerRoot) + 1, -14);			  
				
//				echo $file."\n";
				
				// language file
				$this->locale->translationManager()->loadFile($file);
			}
		}
//		exit;	  	
	}	
	
	private function loadLocale()
	{
		$this->locale =	Locale::getInstance($this->localeName);	
		$this->locale->translationManager()->setCacheFileDir(ClassLoader::getRealPath('cache.language'));
		$this->locale->translationManager()->setDefinitionFileDir(ClassLoader::getRealPath('application.configuration.language'));
		Locale::setCurrentLocale($this->localeName);	
		
		$this->loadLanguageFiles();		
	
		return $this->locale;		  	
	}
	
	private function loadLocaleName()
	{
		if ($this->request->isValueSet("language"))
		{
			$this->localeName = $this->request->getValue("language");			
		}
		else if (isset($_SESSION['lang']))
		{
			$this->localeName = $_SESSION['lang'];
		}
		else
		{
	  		$lang = Language::getDefaultLanguage();
	  		$this->localeName = $lang->getId();
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
		    
			default:
		    break;		    
		}
	}
	
}

?>