<?php

/**
 *  A layered application-configuration container
 *
 *  Allows to create a tree of integrated mini-applications as modules.
 *  The main application is the root node of the tree.
 *
 *  @package application
 *  @author Integry Systems
 */
class ConfigurationContainer
{
	protected $mountPath;
	protected $directory;
	protected $pluginDirectory;
	protected $configDirectory;
	protected $languageDirectory;
	protected $controllerDirectory;
	protected $blockConfiguration = array();
	protected $modules;
	protected $info = array();
	protected $enabled = false;

	public function __construct($mountPath)
	{
		$this->mountPath = $mountPath;
		$this->directory = ClassLoader::getRealPath($mountPath);

		foreach (array( 'configDirectory' => 'application.configuration.registry',
						'languageDirectory' => 'application.configuration.language',
						'controllerDirectory' => 'application.controller',
						'pluginDirectory' => 'plugin') as $var => $path)
		{
			$dir = ClassLoader::getRealPath($mountPath . '.' . $path);
			$this->$var = is_dir($dir) ? $dir : null;
		}

		foreach (array('storage.customize.view', 'application.view') as $dir)
		{
			$path = ClassLoader::getRealPath($mountPath . '.' . $dir) . '/block.ini';
			if (file_exists($path))
			{
				$this->blockConfiguration[] = $path;
			}
		}

		$this->loadInfo();
	}

	public function getBlockFiles()
	{
		return $this->findDirectories('blockConfiguration');
	}

	public function getConfigDirectories()
	{
		return $this->findDirectories('configDirectory');
	}

	public function getControllerDirectories()
	{
		return $this->findDirectories('controllerDirectory');
	}

	public function getPluginDirectories()
	{
		return $this->findDirectories('pluginDirectory');
	}

	public function getLanguageDirectories()
	{
		return $this->findDirectories('languageDirectory');
	}

	private function findDirectories($variable)
	{
		$directories = array($this->$variable);
		foreach ($this->getModules() as $module)
		{
			$directories = array_merge($directories, $module->findDirectories($variable));
		}

		return $directories;
	}

	public function getInfo()
	{
		return $this->info;
	}

	public function getModules()
	{
		if (is_null($this->modules))
		{
			$modulePath = $this->mountPath . '.module';
			$moduleDir = ClassLoader::getRealPath($modulePath);

			$modules = array();
			if (is_dir($moduleDir))
			{
				foreach (new DirectoryIterator($moduleDir) as $node)
				{
					if ($node->isDir() && !$node->isDot())
					{
						$module = new ConfigurationContainer($modulePath . '.' . $node->getFileName());
						$modules[] = $module;
						$modules = array_merge($modules, $module->getModules());
					}
				}
			}

			$this->modules = $modules;
		}

		return $this->modules;
	}

	public function addModule($module)
	{
		$this->getModules();
		$this->modules[] = new ConfigurationContainer($module);
	}

	public function getRoutes()
	{

	}

	public function getOverrideRoutes()
	{

	}

	protected function loadInfo()
	{
		$iniPath = $this->directory . '/Module.ini';
		if (file_exists($iniPath))
		{
			$this->info = parse_ini_file($iniPath, true);
		}
	}
}

?>