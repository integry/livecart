<?php

ClassLoader::import("application.controller.BaseController");

ClassLoader::import("application.model.locale.*");
ClassLoader::import("library.locale.*");
ClassLoader::import("library.*");

ClassLoader::import("application.model.menu.*");
ClassLoader::import("application.helper.*");

/**
 * Generic backend controller for administrative tools (actions, modules etc.)
 *
 * @package application.controller
 */
abstract class BackendController extends BaseController {
	
	protected $locale = null;
	
	protected $localeName;
	
	protected $rootDirectory = "";//"/k-shop";
	
	protected $uploadDir = "upload/images/products/";	  
    
    //protected $thumbsDir = "/public/upload/images/products/thumbs/";
	
	public function __construct(Request $request) {
		parent::__construct($request);
		
		if (!$this->user->hasAccess($this->getRoleName())) {	
			//throw new AccessDeniedException($this->user, $this->request->getControllerName(), $this->request->getActionName());
		}
		

		if($this->request->isValueSet("language"))
		{
			$this->localeName = $this->request->getValue("language");			
		}
		else
		{
	  		$lang = Language::getDefaultLanguage();	  		
	  		$this->localeName = $lang->getId();
		}

		$this->locale =	Locale::getInstance($this->localeName);	
		Locale::setCurrentLocale($this->localeName);		
		$app = Application::getInstance();

	}
	
	public function init()
	{
		$this->setLayout("mainLayout");		
		$this->addBlock('MENU', 'menuSection');	
		Application::getInstance()->getRenderer()->setValue('BASE_URL', Router::getBaseUrl());
	}
	
	protected function menuSectionBlock() 
	{			
		$menuLoader = new MenuLoader();		
		$structure = $menuLoader->getCurrentHierarchy($this->request->getControllerName(),	$this->request->getActionName());
		
		$response =	new BlockResponse();		
		$response->setValue("topList", $menuLoader->getTopList());	
		$response->setValue("menu_javascript", TigraMenuHelper::formatJsMenuArray($structure));	

		return $response;
	}
	
	/*
	protected function renderAjaxJsFiles(Response $response) {
	
		$response->appendValue("JAVASCRIPT", array(Router::getInstance()->getBaseDir()."/public/javascript/document.js", 
		 											Router::getInstance()->getBaseDir()."/public/javascript/ajax.js",
													Router::getInstance()->getBaseDir()."/public/javascript/TreeMenuAjax.js" ));
	}
	*/
	
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
		
		$method = new ReflectionMethod($controllerClassName, $actionName);
		$actionDocComment = $method->getDocComment();
		
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
}

?>