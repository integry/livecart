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
	protected $rootDirectory = "";

	protected $locale = null;	

	/**
	 * Store instance
	 *
	 * @var Store
	 */
	protected $store = null;
	
	public function __construct(Request $request) 
	{
		parent::__construct($request);
										
		if (!$this->user->hasAccess($this->getRoleName())) {	
			//throw new AccessDeniedException($this->user, $this->request->getControllerName(), $this->request->getActionName());
		}
		
		$this->store = Store::getInstance();
		$this->store->setRequestLanguage($this->request->getValue('requestLanguage'));
		$this->loadLanguageFiles();
		
		unset($this->locale);
		
		Router::setAutoAppendVariables(array('requestLanguage' => $this->store->getLocaleInstance()->getLocaleCode()));
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
		Application::getInstance()->getRenderer()->setValue('BASE_URL', Router::getBaseUrl());
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

		$langFiles = array();
		foreach (array_reverse(get_included_files()) as $file)
		{
			$class = basename($file,'.php');
			if (class_exists($class, false) && is_subclass_of($class, 'Controller'))
			{
				$file = substr($file, strlen($controllerRoot) + 1, -14);			  
				$langFiles[] = $file;
			}
		}
		
		$this->store->setLanguageFiles($langFiles);
	}	
	
	private function __get($name)
	{
		switch ($name)
	  	{
		    case 'locale':
		    	$this->locale = $this->store->getLocaleInstance();
		    	$this->loadLanguageFiles();
				return $this->locale;
		    break;
		    
			default:
		    break;		    
		}
	}
	
}

?>