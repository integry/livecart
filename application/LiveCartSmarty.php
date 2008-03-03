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
	}

	/**
	 * Get LiveCart application instance
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

	public function _smarty_include($params)
	{
		// strip custom:
		$path = substr($params['smarty_include_tpl_file'], 7);

		ob_start();
		parent::_smarty_include($params);
		$output = ob_get_contents();
		ob_end_clean();

		echo $this->application->getRenderer()->applyLayoutModifications($path, $output);
	}

   /**
     * Get the compile path for this resource
     *
     * @param string $resource_name
     * @return string results of {@link _get_auto_filename()}
     */
    public function _get_compile_path($resource_name)
    {
        if (substr($resource_name, 0, 7) == 'custom:')
        {
        	if (!function_exists('smarty_custom_get_path'))
        	{
        		include ClassLoader::getRealPath('application.helper.smarty.') . 'resource.custom.php';
			}

        	$resource_name = smarty_custom_get_path(substr($resource_name, 7), $this);
		}

        return $this->_get_auto_filename($this->compile_dir, $resource_name,
                                         $this->_compile_id) . '.php';
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
