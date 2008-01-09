<?php

ClassLoader::import('framework.renderer.SmartyRenderer');
ClassLoader::import('application.LiveCartSmarty');

/**
 *  Implements LiveCart-specific view renderer logic
 *
 *  @package application
 *  @author Integry Systems
 */
class LiveCartRenderer extends SmartyRenderer
{
	private $paths = array();

	private $blockConfiguration = null;

	/**
	 * Template renderer constructor
	 *
	 * Creates a smarty instance and sets a compile directory path (this is required
	 * by smarty)
	 */
	public function __construct(LiveCart $application)
	{
		self::registerHelperDirectory(ClassLoader::getRealPath('application.helper.smarty'));
		self::registerHelperDirectory(ClassLoader::getRealPath('application.helper.smarty.form'));
		parent::__construct($application);
	}

	/**
	 * Gets a smarty instance
	 *
	 * @return Smarty
	 */
	public function getSmartyInstance()
	{
		if (!$this->tpl)
		{
			$this->tpl = new LiveCartSmarty(self::getApplication());
			$this->tpl->compile_dir = self::$compileDir;
			$this->tpl->template_dir = ClassLoader::getRealPath("application.view");
		}

		return $this->tpl;
	}

	public function getTemplatePaths($template = '')
	{
		if (!$this->paths)
		{
			if ($theme = self::getApplication()->getTheme())
			{
				$this->paths[] = ClassLoader::getRealPath('storage.customize.view.theme.' . $theme . '.');
				$this->paths[] = ClassLoader::getRealPath('application.view.theme.' . $theme . '.');
			}
			$this->paths[] = ClassLoader::getRealPath('storage.customize.view.');
			$this->paths[] = ClassLoader::getRealPath('application.view.');
		}

		if (!$template)
		{
			return $this->paths;
		}

		$paths = $this->paths;
		foreach ($paths as &$path)
		{
			$path = $path . $template;
		}

		return $paths;
	}

	public function getTemplatePath($template)
	{
		foreach ($this->getTemplatePaths($template) as $path)
		{
			if (is_readable($path))
			{
				return $path;
			}
		}
	}

	public function render($view)
	{
		if (!realpath($view))
		{
			$view = $this->getTemplatePath($view);
		}

		$output = parent::render($view);

		return $this->applyLayoutModifications($view, $output);
	}

	public function applyLayoutModifications($tplPath, $output)
	{
		if (realpath($tplPath))
		{
			$tplPath = $this->getRelativeTemplatePath($tplPath);
		}

		if ($conf = $this->getBlockConfiguration($tplPath))
		{
			foreach ($conf as $command)
			{
				switch ($command['action']['command'])
				{
					case 'remove':
						$output = '';
					break;
				}

			}
			//var_dump($conf);
		}

		return $output;
	}

	public function getBlockConfiguration($blockOrTemplate = null)
	{
		if (is_null($this->blockConfiguration))
		{
			$config = $this->parseConfigFile($this->getTemplatePath('block.ini'));
			$request = $this->getApplication()->getRequest();
			$controller = $request->getControllerName();
			$validPairs = array(
							array('*', '*'),
							array($controller, '*'),
							array($controller, $request->getActionName()),
							);

			$this->blockConfiguration = array();
			foreach ($validPairs as $pair)
			{
				if (isset($config[$pair[0]][$pair[1]]))
				{
					$this->blockConfiguration = array_merge($this->blockConfiguration, $config[$pair[0]][$pair[1]]);
				}
			}
		}

		if (!is_null($blockOrTemplate))
		{
			if (isset($this->blockConfiguration[$blockOrTemplate]))
			{
				return $this->blockConfiguration[$blockOrTemplate];
			}
		}
		else
		{
			return $this->blockConfiguration;
		}
	}

	public function isBlock($objectName)
	{
		return '.tpl' != strtolower(substr($objectName, -4));
	}

	/**
	 *
	 */
	private function parseConfigFile($file)
	{
		$config = parse_ini_file($file, true);
		$parsed = array();
		foreach ($config as $file => $actions)
		{
			foreach ($actions as $action => $command)
			{
				$req = $this->parseKey($action);
				$con = $req['controller'];
				$act = $req['action'];
				unset($req['controller'], $req['action']);

				$parsed[$con][$act][$file][] = array('params' => $req, 'action' => $this->parseCommand($command));
			}
		}

		return $parsed;
	}

	private function parseKey($key)
	{
		$res = array();

		// first part is always controller name (or *, which is all controllers and actions)
		$parts = explode('/', $key);
		$res['controller'] = array_shift($parts);

		if (!$res['controller'])
		{
			$res['controller'] = '*';
		}

		// second part can be either action name or record ID (special/shorthand case)
		$sec = array_shift($parts);
		if (is_numeric($sec))
		{
			$res['id'] = $sec;
		}
		else
		{
			$res['action'] = $sec;
		}

		if (empty($res['action']))
		{
			$res['action'] = '*';
		}

		// anything else can be key=value pairs (a numeric value without key is considered to be an "id")
		foreach ($parts as $pair)
		{
			if (is_numeric($pair))
			{
				$res['id'] = $pair;
			}
			else if (strpos($pair, '='))
			{
				list($key, $value) = explode('=', $pair, 2);
				$res[$key] = $value;
			}
		}

		return $res;
	}

	private function parseCommand($command)
	{
		$res = array();
		$parts = explode(':', $command);

		$res['command'] = count($parts) > 1 ? array_shift($parts) : 'append';
		$res['view'] = array_shift($parts);

		if ('.tpl' == substr($res['view'], -4))
		{
			$res['view'] = substr($res['view'], 0, -4);
		}

		if (empty($res['call']))
		{
			$res['call'] = array('BaseController', 'getGeneric');
		}

		return $res;
	}

	private function getRelativeTemplatePath($template)
	{
		foreach (array('application.view', 'storage.customize.view') as $path)
		{
			$path = ClassLoader::getRealPath($path);
			if (substr($template, 0, strlen($path)) == $path)
			{
				return substr($template, strlen($path) + 1);
			}
		}
	}
}

?>
