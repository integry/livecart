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

	public function __construct($files)
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