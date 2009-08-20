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
	protected $viewDirectories = array();
	protected $blockConfiguration = array();
	protected $modules;
	protected $enabledModules;
	protected $info = array();
	protected $enabled = false;
	protected $application;

	public function __construct($mountPath, LiveCart $application)
	{
		$this->mountPath = $mountPath;
		$this->directory = ClassLoader::getRealPath($mountPath);
		$this->application = $application;

		$this->directory = preg_replace('/\\' . DIRECTORY_SEPARATOR . '{2,}/', DIRECTORY_SEPARATOR, $this->directory);

		foreach (array( 'configDirectory' => 'application.configuration.registry',
						'languageDirectory' => 'application.configuration.language',
						'controllerDirectory' => 'application.controller',
						'pluginDirectory' => 'plugin') as $var => $path)
		{
			$dir = ClassLoader::getRealPath($mountPath . '.' . $path);
			$this->$var = is_dir($dir) ? realpath($dir) : null;
		}

		foreach (array('storage.customize.view', 'application.view') as $dir)
		{
			foreach (array('ini', 'php') as $ext)
			{
				$path = ClassLoader::getRealPath($mountPath . '.' . $dir) . '/block.' . $ext;
				if (file_exists($path))
				{
					$this->blockConfiguration[] = $path;
				}
			}

			$this->viewDirectories[] = dirname($path);
		}

		$this->loadInfo();
	}

	public function disableModules()
	{
		$this->modules = array();
	}

	public function saveToCache()
	{
		$this->application->getCache()->set('modules', serialize($this));
	}

	public function getMountPath()
	{
		return $this->mountPath;
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

	public function getViewDirectories()
	{
		return $this->findDirectories('viewDirectories');
	}

	public function getPluginDirectories()
	{
		return $this->findDirectories('pluginDirectory');
	}

	public function getLanguageDirectories()
	{
		return $this->findDirectories('languageDirectory');
	}

	public function getModuleDirectories()
	{
		return $this->findDirectories('directory');
	}

	public function getDirectoriesByMountPath($mountPath)
	{
		$directories = array();

		$dir = ClassLoader::getRealPath($this->mountPath . '.' . $mountPath);
		if (file_exists($dir))
		{
			$directories[] = $dir;
		}

		foreach ($this->getModules() as $module)
		{
			$directories = array_merge($directories, $module->getDirectoriesByMountPath($mountPath));
		}

		return $directories;
	}

	private function findDirectories($variable)
	{
		$directories = $this->$variable ? array($this->$variable) : array();
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

	public function getName()
	{
		$info = $this->getInfo();
		return $info['Module']['name'];
	}

	public function getAvailableModules()
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
					$module = new ConfigurationContainer($modulePath . '.' . $node->getFileName(), $this->application);
					$modules[$module->getMountPath()] = $module;
					$modules = array_merge($modules, $module->getAvailableModules());
				}
			}
		}

		return $modules;
	}

	public function getModules()
	{
		if (is_null($this->modules))
		{
			$this->modules = $this->getAvailableModules();
		}

		if (is_null($this->enabledModules))
		{
			$modules = $this->modules;
			$conf = $this->application->getConfig();
			foreach (array('enabledModules', 'installedModules') as $var)
			{
				$confModules = $conf->isValueSet($var) ? $conf->get($var) : array();
				$modules = array_intersect_key($modules, $confModules);
			}

			$this->enabledModules = $modules;
		}

		return $this->enabledModules;
	}

	public function getModule($mountPath)
	{
		if ($this->mountPath == $mountPath)
		{
			return $this;
		}

		foreach ((array)$this->modules as $module)
		{
			if ($m = $module->getModule($mountPath))
			{
				return $m;
			}
		}
	}

	public function addModule($module)
	{
		$this->getModules();
		$this->modules[] = new ConfigurationContainer($module, $this->application);
	}

	public function isEnabled()
	{
		return $this->getConfig('enabledModules') && $this->isInstalled();
	}

	public function isInstalled()
	{
		return $this->getConfig('installedModules');
	}

	public function setStatus($isActive)
	{
		$this->setConfig('enabledModules', $isActive);
	}

	public function install()
	{
		$this->installDatabase();
		$this->setConfig('installedModules', true);

		// custom installation procedures
		$this->customInstall('install.php');

		// set up symlink to public directory
		$publicDir = $this->directory . DIRECTORY_SEPARATOR . 'public';

		if (file_exists($publicDir))
		{
			if (function_exists('symlink'))
			{
				if (!@symlink($publicDir, $this->getPublicDirectoryLink()))
				{
					return false;
				}
			}

			// Windows
			else
			{
				full_copy($publicDir, $this->getPublicDirectoryLink());
			}
		}
	}

	public function deinstall()
	{
		$this->deinstallDatabase();
		$this->setConfig('installedModules', false);

		// custom installation procedures
		$this->customInstall('deinstall.php');

		$symLink = $this->getPublicDirectoryLink();
		if (file_exists($symLink))
		{
			unlink($symLink);
		}
	}

	private function customInstall($file)
	{
		$filePath = $this->directory . '/installdata/' . $file;
		if (file_exists($filePath))
		{
			include_once $filePath;
		}
	}

	private function getPublicDirectoryLink()
	{
		return ClassLoader::getRealPath('public.module.') . basename($this->directory);
	}

	protected function installDatabase()
	{
		return $this->loadSQL($this->directory . '/installdata/sql/create.sql') &&
			   $this->loadSQL($this->directory . '/installdata/sql/initialData.sql');
	}

	protected function deinstallDatabase()
	{
		return $this->loadSQL($this->directory . '/installdata/sql/drop.sql');
	}

	protected function loadSQL($file)
	{
		ClassLoader::import('application.model.system.Installer');

		if (file_exists($file))
		{
			return Installer::loadDatabaseDump(file_get_contents($file));
		}
		else
		{
			return true;
		}
	}

	private function getConfig($var)
	{
		$config = $this->application->getConfig();

		$modules = $config->isValueSet($var) ? $config->get($var) : array();

		if (!empty($modules[$this->mountPath]))
		{
			return $modules[$this->mountPath];
		}
		else
		{
			return array();
		}
	}

	private function setConfig($var, $status)
	{
		$config = $this->application->getConfig();

		$activeModules = $config->isValueSet($var) ? $config->get($var) : array();

		if ($status)
		{
			$activeModules[$this->mountPath] = true;
		}
		else
		{
			unset($activeModules[$this->mountPath]);
		}

		$config->set($var, $activeModules);
		$config->save();
	}

	public function getRouteFiles()
	{
		$files = array();
		foreach ($this->getConfigDirectories() as $dir)
		{
			$dir = dirname($dir) . '/route';
			$files = array_merge($files, glob($dir . '/*.php'));
		}

		return $files;
	}

	public function getOverrideRoutes()
	{

	}

	public function loadInfo()
	{
		$iniPath = $this->directory . '/Module.ini';
		if (file_exists($iniPath))
		{
			$this->info = parse_ini_file($iniPath, true);
		}

		$this->info['path'] = $this->mountPath;
	}
}

function full_copy( $source, $target )
{
	if ( is_dir( $source ) )
	{
		@mkdir( $target );

		$d = dir( $source );

		while ( FALSE !== ( $entry = $d->read() ) )
		{
			if ( $entry == '.' || $entry == '..' )
			{
				continue;
			}

			$Entry = $source . '/' . $entry;
			if ( is_dir( $Entry ) )
			{
				full_copy( $Entry, $target . '/' . $entry );
				continue;
			}
			copy( $Entry, $target . '/' . $entry );
		}

		$d->close();
	}else
	{
		copy( $source, $target );
	}
}

?>