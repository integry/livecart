<?php

class Theme
{
	private $name;
	private $application;
	private $parentThemes;

	public function __construct($name, LiveCart $application)
	{
		$this->name = $name;
		$this->application = $application;
	}

	public function getName()
	{
		return $this->name;
	}

	public function setParentThemes(array $themes)
	{
		$this->parentThemes = $themes;
	}

	public function getParentThemes()
	{
		if (is_null($this->parentThemes))
		{
			$inheritConf = array();
			$inheritConf[] = ClassLoader::getRealPath('storage.customize.view.theme.' . $this->name . '.inherit') . '.php';
			$inheritConf[] = ClassLoader::getRealPath('application.view.theme.' . $this->name . '.inherit') . '.php';

			$this->parentThemes = array();

			foreach ($inheritConf as $inherit)
			{
				if (file_exists($inherit))
				{
					$this->parentThemes = include $inherit;
					break;
				}
			}
		}

		return $this->parentThemes;
	}

	public function getAllParentThemes()
	{
		$themes = array_merge(array('barebone'), $this->getParentThemes());
		$themes = array_diff($themes, array($this->name));

		return $themes;
	}

	public function isCoreTheme()
	{
		return file_exists(ClassLoader::getRealPath('application.view.theme.' . $this->name));
	}

	public function isExistingTheme()
	{
		$themes = $this->application->getRenderer()->getThemeList();
		return isset($themes[$this->name]);
	}

	public function saveConfig()
	{
		$file = ClassLoader::getRealPath('storage.customize.view.theme.' . $this->name . '.inherit') . '.php';
		$dir = dirname($file);
		if (!file_exists($dir))
		{
			mkdir($dir, 0777, true);
		}

		file_put_contents($file, '<?php return ' . var_export($this->getParentThemes(), true) . '; ?>');
	}

	public function create()
	{
		foreach ($this->getThemeDirectories() as $dir)
		{
			mkdir($dir, 0777, true);
		}
	}

	public function delete()
	{
		foreach ($this->getThemeDirectories() as $dir)
		{
			$this->rmdir_recurse($dir);
		}
	}

	public function getThemeDirectories()
	{
		return array(ClassLoader::getRealPath('storage.customize.view.theme.' . $this->name),
					 ClassLoader::getRealPath('public.upload.theme.' . $this->name));
	}

	public function getStyleConfig()
	{
		$conf = array();
		foreach ($this->getAllParentThemes() as $theme)
		{
			$inst = new Theme($theme, $this->application);
			$conf = $this->array_merge_recursive_distinct($conf, $inst->getStyleConfig());
		}

		$path = $this->application->getRenderer()->getTemplatePath('theme/'. $this->name . '/.theme/style.ini');
		if ($path)
		{
			$conf = $this->array_merge_recursive_distinct($conf, parse_ini_file($path, true));
		}

		return $conf;
	}

	public function toArray()
	{
		return array('name' => $this->name,
					 'isCore' => $this->isCoreTheme(),
					 'parents' => $this->getParentThemes());
	}

	private function rmdir_recurse($path)
	{
		$path= rtrim($path, '/').'/';

		if (!file_exists($path))
		{
			return;
		}

		$handle = opendir($path);
		for (;false !== ($file = readdir($handle));)
			if($file != "." and $file != ".." ) {
				$fullpath= $path.$file;
				if( is_dir($fullpath) ) {
					$this->rmdir_recurse($fullpath);
				} else {
					unlink($fullpath);
				}
		}
		closedir($handle);
		rmdir($path);
	}

	private function array_merge_recursive_distinct ( array &$array1, array &$array2 )
	{
	  $merged = $array1;

	  foreach ( $array2 as $key => &$value )
	  {
		if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) )
		{
		  $merged [$key] = $this->array_merge_recursive_distinct ( $merged [$key], $value );
		}
		else
		{
		  $merged [$key] = $value;
		}
	  }

	  return $merged;
	}
}

?>