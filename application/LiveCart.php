<?php

ClassLoader::import('framework.Application');

class LiveCart extends Application
{
	/**
	 * Application instance (based on a singleton pattern)
	 */
	private static $instance = null;

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
		}
		
		return self::$instance;
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
	 * @todo Cache plugin file locations
	 */
    private function processPlugins(Controller $controllerInstance, Response $response)
	{
        $name = $controllerInstance->getControllerName();
        		
        // check for response post-processor plugins        
        $pluginDir = ClassLoader::getRealPath('plugin.controller.' . $name);
		
		if (!is_dir($pluginDir))
		{
            return false;
        }
		
		foreach (new DirectoryIterator($pluginDir) as $file)
		{
            if (substr($file->getFileName(), -4) == '.php')
            {
                include_once($file->getPathname());
                $class = substr($file->getFileName(), 0, -4);
                $plugin = new $class($response);
                $plugin->process();
            }
        }
    }
}

?>