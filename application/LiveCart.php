<?php

ClassLoader::import('framework.Application');

/**
 *  Implements LiveCart-specific application flow logic
 */
class LiveCart extends Application
{
	/**
	 * Application instance (based on a singleton pattern)
	 */
	private static $instance = null;
	
	private static $pluginDirectories = array();

	private $isBackend = false;

	/**
	 * Returns an instance of LiveCart Application
	 *
	 * Method prevents of creating multiple application instances during one request
	 *
	 * @return LiveCart
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new LiveCart();
			
			$compileDir = Store::getInstance()->isCustomizationMode() ? 'cache.templates_c.customize' : 'cache.templates_c';
			TemplateRenderer::setCompileDir(ClassLoader::getRealPath($compileDir));
		}
		
		return self::$instance;
	}
	
	/**
	 * Registers a new plugin directory (multiple plugin directories are supported)
	 *
	 * @param string $dir Full plugin directory path
	 */
	public static function registerPluginDirectory($dir)
	{
		self::$pluginDirectories[$dir] = true;
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
		// get custom template path
        $path = ClassLoader::getRealPath('storage.customize.view.' . $controllerName . '.' . $actionName) . '.tpl';
        
        if (!is_readable($path))
        {
            return parent::getView($controllerName, $actionName);
        }
        
        return $path;
	}

	/**
	 * Gets a physical layout template path
	 *
	 * @param string $layout layout handle (filename without extension)
	 * @return string
	 */
	public function getLayoutPath($layout)
	{
		// get custom template path
        $path = ClassLoader::getRealPath('storage.customize.view.layout.' . $layout) . '.tpl';
        
        if (!is_readable($path))
        {
            return parent::getLayoutPath($layout);
        }
        
        return $path;
	}	
		
	/**
	 * Gets renderer for application
	 *
	 * @return Renderer
	 */
	public function getRenderer()
	{
		if (is_null($this->renderer))
		{
			ClassLoader::import('application.LiveCartRenderer');
			$this->renderer = new LiveCartRenderer($this->router);
		}
		
		$renderer = parent::getRenderer();

		if (Store::getInstance()->isCustomizationMode() && !$this->isBackend)
		{
			$smarty = TemplateRenderer::getSmartyInstance();
			$smarty->autoload_filters = array('pre' => array('templateLocator'));			
		}
		
		return $renderer;
	}	
		
	/**
	 * Gets specified controller instance
	 *
	 * @param string $controllerName Controller name
	 * @return Controller
	 * @throws ControllerNotFoundException if controller does not exist
	 */
	protected function getControllerInstance($controllerName)
	{
		if (substr($controllerName, 0, 8) == 'backend.')
		{
			$this->isBackend = true;
		}

		return parent::getControllerInstance($controllerName);
	}
		
	/**
	 * Executes controllers action and returns response
	 *
	 * @param string $controllerName Controller name
	 * @param string $actionName Action name
	 * @return Response
	 * @throws ApplicationException if error situation occurs
	 */
	protected function execute($controllerInstance, $actionName)
	{
		$response = parent::execute($controllerInstance, $actionName);
		
		$this->processPlugins($controllerInstance, $response);
	
		return $response;
	}    
	
	/**
 `	 * Execute response post-processor plugins        
 	 *
	 * @todo Cache plugin file locations
	 */
    private function processPlugins(Controller $controllerInstance, Response $response)
	{
        $name = $controllerInstance->getControllerName();
        $action = $controllerInstance->getRequest()->getActionName();
        
		ClassLoader::import('application.ControllerPlugin');
				
		$dirs = array_merge(array(ClassLoader::getRealPath('plugin.controller.' . $name . '.' . $action) => 0), self::$pluginDirectories);
				
        foreach ($dirs as $pluginDir => $type)
        {
			if ($type)
			{
				$pluginDir = $pluginDir . '/controller/' . $name . '/' . $action;
			}				

			if (!is_dir($pluginDir))
			{
	            continue;
	        }

			foreach (new DirectoryIterator($pluginDir) as $file)
			{
	            if (substr($file->getFileName(), -4) == '.php')
	            {
	                include_once($file->getPathname());
	                $class = substr($file->getFileName(), 0, -4);
	                $plugin = new $class($response, $controllerInstance);
	                $plugin->process();
	            }
	        }
		}
    }
}

?>