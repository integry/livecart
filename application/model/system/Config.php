<?php

/**
 * System configuration container
 *
 * @package application.model.system
 */
class Config
{
	/**
	 *  Configuration value array (key => value)
	 */
	private $values = array();

	/**
	 *  Configuration values mapped to files (  file => (key => value)  )
	 */
	private $fileValues = array();

	/**
	 *  List of files with modified values (to be saved)
	 */
	private $changedFiles = array();

	/**
	 *  Configuration file directory path
	 */
	private $configFileDir = '';

	public function __construct($files = array())
	{
		$this->configFileDir = ClassLoader::getRealPath('cache.registry') . '/';
		foreach ($files as $file)
		{
		  	$this->loadFile($file);
		}
	}

	public function getValue($key)
	{
		if (isset($this->values[$key]))
		{
		  	return $this->values[$key];
		}
	}

	public function setValue($key, $value, $file = '')
	{
		if (!$file)
		{
		  	foreach ($this->fileValues as $configFile => $values)
		  	{
			    if (isset($values[$key]))
				{
				  	$file = $configFile;
				  	break;
				}
			}
		}

		if (!$file)
		{
		  	return false;
		}

		$this->values[$key] = $value;
		$this->fileValues[$file][$key] = $value;
		$this->changedFiles[$file] = true;
	}

	public function save()
	{
		foreach ($this->changedFiles as $file => $isChanged)
		{
			$this->saveFile($file);
		}
	}

	public function getSection($sectionId)
	{
		return parse_ini_file($this->getSectionFile($sectionId));	
	}

	public function getSectionLayout($sectionId)
	{
		$ini = parse_ini_file($this->getSectionFile($sectionId), true);	
		
		// remove title section
		if (count($ini) > 1)
		{
			array_shift($ini);  
		}
		else
		{
		  	$arr = array_shift($ini);
			$ini = array('' => $arr);
		}	
		
		return $ini;
	}

	public function getTree($dir = null, $keyPrefix = null)
	{
	  	if (!$dir)
	  	{
			$dir = ClassLoader::getRealPath('application.configuration.registry') . '/';
		}
		
		$res = array();
		$d = new DirectoryIterator($dir);
		foreach ($d as $file)
		{
			if ($file->isFile() && 'ini' == substr($file->getFileName(), -3))
			{
				$ini = parse_ini_file($file->getPathName(), true);
				$key = substr($file->getFileName(), 0, -4);
				
				$out = array();
				$out['name'] = key($ini);
				
				$subpath = $file->getPath() . '/' . substr($key, 3);
				
				if (file_exists($subpath))
				{
				  	$out['subs'] = $this->getTree($subpath, substr($key, 3));
				}
				
				if ($keyPrefix)
				{
				  	$key = $keyPrefix . '.' . $key;
				}
				
				$res[$key] = $out;
			}  
		}
		
		return $res;
	}

	private function getSectionFile($sectionId)
	{
		return ClassLoader::getRealPath('application.configuration.registry.' . $sectionId) . '.ini';
	}

  	private function loadFile($file)
  	{
		$filePath = $this->getFullPath($file);
		if (file_exists($filePath))
		{
			include $filePath;
			$this->values = array_merge($this->values, $config);
			$this->fileValues[$file] = $config;
		  	return true;
		}
		else
		{
		  	return false;
		}
	}

  	private function saveFile($file)
  	{
	    $content = '<?php $config = ' . var_export($this->fileValues[$file], true) . '; ?>';
	    $fullPath = $this->getFullPath($file);
	    if (!is_dir(dirname($fullPath)))
	    {
		  	echo dirname($fullPath);
			  mkdir(dirname($fullPath), 0777, true);
		}
		file_put_contents($this->getFullPath($file), $content);
	    return true;
	}

	private function getFullPath($file)
	{
	  	return $this->configFileDir . $file . '.php';
	}
}

?>