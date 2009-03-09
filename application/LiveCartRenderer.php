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
				$this->paths = array_merge($this->paths, $this->getThemePaths($theme));
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
			$path = $this->getPath($path, $template);
		}

		return $paths;
	}

	public function resetPaths()
	{
		$this->paths = array();
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

	public function getBaseTemplatePath($tplName)
	{
		$tplName = substr($tplName, 1);
		foreach (array(ClassLoader::getRealPath('storage.customize.view.'), ClassLoader::getRealPath('application.view.')) as $path)
		{
			$file = $path . $tplName;
			if (file_exists($file))
			{
				return $file;
			}
		}
	}

	public function getThemeList()
	{
		$themes = array('default' => 'default', 'barebone' => 'barebone');

		$otherThemes = array();
		foreach (array(ClassLoader::getRealPath('application.view.theme'), ClassLoader::getRealPath('storage.customize.view.theme')) as $themeDir)
		{
			if (file_exists($themeDir))
			{
				foreach (new DirectoryIterator($themeDir) as $dir)
				{
					if ($dir->isDir() && !$dir->isDot())
					{
						$otherThemes[$dir->getFileName()] = $dir->getFileName();
					}
				}
			}
		}

		ksort($otherThemes);
		return array_merge($themes, $otherThemes);

		return $themes;
	}

	public function render($view)
	{
		if (!file_exists($view))
		{
			$original = $view;
			$view = $this->getTemplatePath($view);
			if (!$view)
			{
				throw new ViewNotFoundException($original);
			}
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
		}

		return $output;
	}

	public function getBlockConfiguration($blockOrTemplate = null, $file = null)
	{
		if (is_null($this->blockConfiguration) || $file)
		{
			$files = $file ? array($file) : $this->getApplication()->getConfigContainer()->getBlockFiles();

			$config = array();
			foreach ($files as $file)
			{
				$config = array_merge_recursive($config, $this->parseConfigFile($file[0]));
			}

			$request = $this->getApplication()->getRequest();
			$controller = $request->getControllerName();
			$validPairs = array(
							array('*', '*'),
							array($controller, '*'),
							array($controller, $request->getActionName()),
							);

			foreach ($config as &$byController)
			{
				foreach ($byController as &$byAction)
				{
					foreach ($byAction as &$byContainer)
					{
						foreach ($byContainer as $index => $block)
						{
							foreach ($block['params']['variables'] as $key => $value)
							{
								if ($request->get($key) != $value)
								{
									unset($byContainer[$index]);
									break;
								}
							}
						}
					}
				}
			}

			$this->blockConfiguration = array();
			foreach ($validPairs as $pair)
			{
				if (isset($config[$pair[0]][$pair[1]]))
				{
					$element = $config[$pair[0]][$pair[1]];
					$this->blockConfiguration = array_merge($this->blockConfiguration, $element);
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

	private function getThemePaths($theme)
	{
		$paths = $inheritConf = array();
		$paths[] = ClassLoader::getRealPath('storage.customize.view.theme.' . $theme . '.');
		$paths[] = ClassLoader::getRealPath('application.view.theme.' . $theme . '.');

		$inheritConf[] = ClassLoader::getRealPath('storage.customize.view.theme.' . $theme . '.inherit') . '.php';
		$inheritConf[] = ClassLoader::getRealPath('application.view.theme.' . $theme . '.inherit') . '.php';

		foreach ($inheritConf as $inherit)
		{
			if (file_exists($inherit))
			{
				break;
			}
		}

		if (file_exists($inherit))
		{
			$inherited = include $inherit;
			if (!is_array($inherited))
			{
				$inherited = array($inherited);
			}

			foreach ($inherited as $parent)
			{
				$paths = array_merge($paths, $this->getThemePaths($parent));
			}
		}

		return $paths;
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

		// check for variables
		$variables = array();
		$parts = explode(' ', $key);
		$key = array_shift($parts);

		foreach ($parts as $part)
		{
			$pair = explode(':', $part, 2);
			$variableKey = trim(array_shift($pair));
			$variableValue = trim(array_shift($pair));
			if ($variableKey)
			{
				$variables[$variableKey] = $variableValue;
			}
		}

		$res['variables'] = $variables;

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

		if ('remove' == $command)
		{
			$res['command'] = 'remove';
		}
		else
		{
			$res['command'] = count($parts) > 1 ? array_shift($parts) : 'append';
		}

		$res['view'] = array_shift($parts);

		if (strpos($res['view'], '->'))
		{
			list($controller, $action) = explode('->', $res['view'], 2);
			$res['call'] = array($controller, $action);
			$res['view'] = './block/' . $action;
		}
		else if ('.tpl' == substr($res['view'], -4))
		{
			$res['view'] = substr($res['view'], 0, -4);
		}

		if (empty($res['call']))
		{
			$res['call'] = array('base', 'getGeneric');
		}

		return $res;
	}

	public function getRelativeTemplatePath($template)
	{
		if (strpos($template, '/module/'))
		{
			preg_match('/\/(module\/.*)/', $template, $match);
			return str_replace('application/view/', '', $match[1]);
		}

		$template = str_replace('\\', '/', $template);
		foreach (array('application.view', 'storage.customize.view') as $path)
		{
			$path = ClassLoader::getRealPath($path);
			$path = str_replace('\\', '/', $path);

			if (substr($template, 0, strlen($path)) == $path)
			{
				return substr($template, strlen($path) + 1);
			}
		}
	}

	private function getPath($root, $template)
	{
		if (substr($template, 0, 7) == 'module/')
		{
			if ($this->paths[count($this->paths) - 1] == $root)
			{
				$root = ClassLoader::getRealPath('.');
				$template = preg_replace('/module\/([a-zA-Z0-9]+)\/(.*)/', 'module/\\1/application/view/\\2', $template);
			}
		}

		return $root . '/' . $template;
	}
}

?>
