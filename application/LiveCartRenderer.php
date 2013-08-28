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

	protected $application;

	protected $cache;

	/**
	 * Template renderer constructor
	 *
	 * Creates a smarty instance and sets a compile directory path (this is required
	 * by smarty)
	 */
	public function __construct(LiveCart $application)
	{
		$this->application = $application;
		$this->registerHelperDirectory(ClassLoader::getRealPath('application.helper.smarty'));
		$this->registerHelperDirectory(ClassLoader::getRealPath('application.helper.smarty.form'));

		parent::__construct($application);
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

		$themes = array_merge($themes, $otherThemes);
		ksort($themes);

		return $themes;
	}

	public function render($view)
	{
		if (file_exists($view))
		{
			$view = $this->getRelativeTemplatePath($view);
		}

		if (!file_exists($view))
		{
			$original = $view;
			$view = $this->getTemplatePath($view);
			if (!$view)
			{
				throw new ViewNotFoundException($original);
			}
		}

		/*$cache = $this->getCache($view);
		if ($cache->isCached())
		{
			return $cache->getData();
		}
		*/

		$output = parent::render($view);
		$output = $this->applyLayoutModifications($view, $output);

		//$cache->setData('<div style="border: 2px solid red;">' . $output . '</div>');
		//$cache->save();

		//$this->cache = $cache->getParent();

		return $output;
	}

	public function getCacheInstance()
	{
		return $this->cache;
	}

	protected function getCache($view)
	{
		$cache = new OutputCache($view);

		if ($this->cache)
		{
			$cache->setParent($this->cache);
		}

		$this->cache = $cache;

		return $cache;
	}

	public function applyLayoutModifications($tplPath, $output)
	{
		if (realpath($tplPath))
		{
			$tplPath = $this->getRelativeTemplatePath($tplPath);
		}

		if ('/' == $tplPath[0])
		{
			$tplPath = substr($tplPath, 1);
		}

		if ($conf = $this->getBlockConfiguration($tplPath))
		{
			foreach ($conf as $command)
			{
				if (!empty($command['action']['call']))
				//if (!empty($command['action']['call']) && ('getGenericBlock' != $command['action']['call'][1]))
				{
					$call = $command['action']['call'];
					$controllerInstance = $this->application->getControllerInstance($call[0]);
					$newOutput = $this->application->renderBlock($command['action'], $controllerInstance);
				}
				else if (in_array($command['action']['command'], array('append', 'prepend', 'replace')))
				{
					$newOutput = $this->render($command['action']['view'] . '.tpl');
				}

				switch ($command['action']['command'])
				{
					case 'append':
						$output .= $newOutput;
					break;

					case 'prepend':
						$output = $newOutput . $output;
					break;

					case 'replace':
						$output = $newOutput;
					break;

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
			foreach ($files as $pluginFiles)
			{
				foreach ((array)$pluginFiles as $file)
				{
					$config = array_merge_recursive($config, $this->parseConfigFile($file));
				}
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
								if ($request->gget($key) != $value)
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

	public function sortBlocks($blocks)
	{
		return $blocks;
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
		$config = substr($file, -3) == 'ini' ? parse_ini_file($file, true) : include $file;

		$parsed = array();
		foreach ($config as $file => $actions)
		{
			foreach ($actions as $action => $command)
			{
				$req = $this->parseKey($action);
				$con = $req['controller'];
				$act = $req['action'];
				unset($req['controller'], $req['action']);

				foreach (explode(' ', $command) as $command)
				{
					$parsed[$con][$act][$file][] = array('params' => $req, 'action' => $this->parseCommand($command));
				}
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
				$res['variables']['id'] = $pair;
			}
			else if (strpos($pair, '_'))
			{
				list($key, $value) = explode('_', $pair, 2);
				$res['variables'][$key] = $value;
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
			$appDir = dirname($this->application->getControllerPath($controller));
			$res['view'] = $appDir . '/view/' . $controller . '/block/' . $action . '.tpl';
		}
		else if ($this->isBlock($res['view']))
		{
			$res['isDefinedBlock'] = true;
		}
		else if ('.tpl' == substr($res['view'], -4))
		{
			$res['view'] = substr($res['view'], 0, -4);
		}

		if (empty($res['call']))
		{
			ClassLoader::import('application.controller.IndexController');
			$res['call'] = array('index', 'getGenericBlock');
		}

		return $res;
	}


}

?>
