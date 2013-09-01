<?php

require_once $this->config->getPath('library/smarty.libs/') . 'Smarty.class.php';

/*
class LiveCartSmarty_Security_Policy extends Smarty_Security
{
	public $allow_php_tag = true;

	public $php_functions = array('is_string');

	public $php_handling = Smarty::PHP_ALLOW;
}
*/

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

	private $private_vars = array();

	public function __construct(LiveCart $application)
	{
		$this->application = $application;

		parent::__construct();

		// missing variable notices in templates
		$this->error_reporting = E_ALL & ~E_NOTICE & ~E_STRICT;

		//$this->enableSecurity('LiveCartSmarty_Security_Policy');

		$this->registerPlugin('modifier', 'config', array($this, 'config'));
		$this->registerPlugin('modifier', 'branding', array($this, 'branding'));

		//$this->registerPlugin('compiler', 'testtest', array($this, 'applyViewPluginszz'));

		$this->setAutoloadFilters(array('config'), 'pre');
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
		return $this->getTemplateVars($var);
	}

	public function setGlobal($var, $value)
	{
		$this->private_vars[$var] = $value;
	}

	public function getGlobal($var)
	{
		return isset($this->private_vars[$var]) ? $this->private_vars[$var] : '';
	}

	/**
	 *  Retrieve software configuration values from Smarty templates
	 *
	 *  <code>
	 *	  [[ config('STORE_NAME') ]]
	 *  </code>
	 */
	public function config($key)
	{
		$config = self::getApplication()->getConfig();
		if ($config->isValueSet($key))
		{
			return $config->get($key);
		}
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
		$path = preg_replace('/^\/*/', '', $path);
		$path = preg_replace('/\/{2,}/', '/', $path);

		if (substr($path, 0, 6) == 'theme/')
		{
			$themePath = $path;
			preg_match('/^theme\/.*\/(.*)$/U', $path, $res);
			$path = preg_replace('/^\/*/', '', array_pop($res));
		}

		$plugins = $this->getPlugins($path);
		if (isset($themePath))
		{
			$plugins = array_merge($plugins, $this->getPlugins($themePath));
		}

		foreach ($plugins as $plugin)
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

	public function applyViewPlugins($resource_name, $source_content)
	{
		return $this->processPlugins($source_content, $resource_name);
	}

	public function filterParams($params)
	{
		foreach ($params as $key => $param)
		{
			if (substr($key, 0, 3) == 'ng_')
			{
				$params[str_replace('_', '-', $key)] = $param;
				unset($params[$key]);
			}
		}

		return $params;
	}

	public function applyFieldValidation($params, Form $form)
	{
		$validation = $this->getFieldValidation($params['name'], $form);
		foreach ($validation as $val)
		{
			$params[$val[0]] = $val[2];
		}

		return $params;
	}

	public function getFieldValidation($name, $form)
	{
		$var = $form->getValidator()->getValidatorVar($name);
		if (!$var)
		{
			return;
		}

		$validation = array();
		foreach ($var->getChecks() as $check)
		{
			$msg = $check->getViolationMsg();
			$params = $check->getParamList();

			if ($check instanceof Validator\PresenceOf)
			{
				$validation[] = array('ng-required', $msg, true);
			}
		}

		return $validation;
	}

	public function formatControl($html, $params)
	{
		if (empty($params['noFormat']))
		{
			$html = '<div class="controls col-lg-10">' . $html . '</div>';
		}

		return $html;
	}

	public function appendParams($content, $params)
	{
		unset($params['noFormat']);
		foreach ($params as $name => $param)
		{
			$content .= ' ' . $name . '="' . htmlspecialchars($param, ENT_QUOTES, 'UTF-8') . '"';
		}

		return $content;
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
        		include $this->config->getPath('application/helper/smarty/') . 'resource.custom.php';
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

	public function disableTemplateLocator()
	{
		if (!empty($this->_plugins['prefilter']['templateLocator']))
		{
			$this->isTemplateLocator = true;
			$this->unregister_prefilter('templateLocator');
			unset($this->_plugins['prefilter']['templateLocator']);
		}
	}

	public function enableTemplateLocator()
	{
		if (!empty($this->templateLocator))
		{
			$this->_plugins['prefilter']['templateLocator'] = $this->templateLocator;
			unset($this->templateLocator);
		}
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
					}

		if ('/' == $path[0])
		{
			$path = substr($path, 1);
		}

		$plugins = array();
		foreach ($this->getApplication()->getPlugins('view/' . $path . '*') as $plugin)
		{
			include_once $plugin['path'];
			$plugins[] = new $plugin['class']($this, $this->application);
		}

		return $plugins;
	}
}

?>
