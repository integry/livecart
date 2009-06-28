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
		$this->register_modifier('branding', array($this, 'branding'));
		$this->load_filter('pre', 'config');

		parent::__construct();
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

	public function set($var, $value)
	{
		$this->assign($var, $value);
	}

	public function get($var)
	{
		return $this->get_template_vars($var);
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

	/**
	 *  Replace "LiveCart" with alternative name if rebranding options are used
	 */
	public function branding($string)
	{
		$softName = self::getApplication()->getConfig()->get('SOFT_NAME');
		return 'LiveCart' != $softName ? str_replace('LiveCart', $softName, $string) : $string;
	}

	public function processPlugins($output, $path)
	{
		$path = substr($path, 0, -4);
		$path = str_replace('\\', '/', $path);

		$path = $this->translatePath($path);

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

	public function _compile_source($resource_name, &$source_content, &$compiled_content, $cache_include_path=null)
	{
		$source_content = $this->processPlugins($source_content, $resource_name);
		return parent::_compile_source($resource_name, $source_content, $compiled_content, $cache_include_path);
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

    public function evalTpl($code)
    {
		$this->_compile_source('evaluated template', $code, $compiled);

		ob_start();
		$this->_eval('?>' . $compiled);
		$_contents = ob_get_contents();
		ob_end_clean();

		return $_contents;
	}

	private function translatePath($path)
	{
		if (substr($path, 0, 7) == 'custom:')
		{
			$path = substr($path, 7);
		}

		if (substr($path, 0, 1) == '@')
		{
			$path = substr($path, 1);
		}

		if ($relative = LiveCartRenderer::getRelativeTemplatePath($path))
		{
			$path = $relative;
		}

		return $path;
	}

	private function getPlugins($path)
	{
		if (!class_exists('ViewPlugin', false))
		{
			ClassLoader::import('application.ViewPlugin');
		}

		if ('/' == $path[0])
		{
			$path = substr($path, 1);
		}

		$plugins = array();
		foreach ($this->getApplication()->getPlugins('view/' . $path) as $plugin)
		{
			include_once $plugin['path'];
			$plugins[] = new $plugin['class']($this, $this->application);
		}

		return $plugins;
	}
}

?>
