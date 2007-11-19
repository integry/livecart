<?php

ClassLoader::import('library.smarty.libs.Smarty');

/**
 *  Extends Smarty with LiveCart-specific logic
 *
 *  @package application
 *  @author Integry Systems
 */
class LiveCartSmarty extends Smarty
{
	private $application;
	
	private $paths = array();
	
	public function __construct(LiveCart $application)
	{
		$this->application = $application;
		$this->register_modifier('config', array($this, 'config'));
		
		foreach (array('application.view', 'storage.customize.view') as $path)
		{
			$this->paths[] = ClassLoader::getRealPath($path);
		}
	}
	
	/**
	 * Get livecart application instance
	 *
	 * @return LiveCart
	 */
	public function getApplication()
	{
		return $this->application;
	}
	
	/**
	 *  Retrieve software configuration values from Smarty templates
	 *
	 *  <code>
	 *	  {'STORE_NAME'|config}
	 *  </code>
	 */	
	public function config($key)
	{
		return self::getApplication()->getConfig()->get($key);
	}
	
	public function processPlugins($output, $path)
	{
		$path = substr($path, 0, -4);		
		$path = str_replace('\\', '/', $path);

		foreach ($this->getPlugins($path) as $plugin)
		{
			$output = $plugin->process($output);
		}

		return $output;
	}
	
	private function getPlugins($path)
	{
		$pluginPath = ClassLoader::getRealPath('plugin.view.' . $path);

		if (!is_dir($pluginPath))
		{
			return array();
		}
		
		if (!class_exists('ViewPlugin', false))
		{
			ClassLoader::import('application.ViewPlugin');
		}
		
		$plugins = array();
		
		foreach (new DirectoryIterator($pluginPath) as $plugin)
		{
			if ($plugin->isFile())
			{
				$className = basename($plugin->getFileName(), '.php');
				ClassLoader::import('plugin.view.' . $path . '.' . $className);
				$plugins[] = new $className($this, $this->application);
			}
		}
		
		return $plugins;
	}
}

?>
