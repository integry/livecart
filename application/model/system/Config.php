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
	
	private $autoSave = true;

	private function __construct()
	{
		$filePath = $this->getFilePath();
		if (file_exists($filePath))
		{
			include $filePath;
			$this->values = $config;
		}		
	}

	public function getInstance()
	{
		static $instance;
		
		if (!$instance)
		{
			$instance = new Config();
		}
		
		return $instance;
	}

	public function getValue($key)
	{
		if (!isset($this->values[$key]))
		{
		  	$this->updateSettings();
		}
		
		if (isset($this->values[$key]))
		{
		  	return $this->values[$key];
		}
		else
		{
			throw new ApplicationException('Configuration value ' . $key . ' not found');
		}
	}

	public function setValue($key, $value)
	{
		$this->values[$key] = $value;
		
		if ($this->autoSave)
		{
			$this->save();
		}
	}

	/**
	 *	Create initial settings cache file or update the cache file with new settings from INI files
	 */
	public function updateSettings()
	{
		$autoSave = $this->autoSave;
		$this->setAutoSave(false);
		
		$sections = $this->getAllSections();	
		foreach ($sections as $section)
		{
			$s = $this->getSettingsBySection($section);
			
			foreach ($s as $key => $value)
			{
				if (!isset($this->values[$key]))
				{
					$this->values[$key] = $value['value'];
				}	
			}
		}
		
		$this->save();
		$this->setAutoSave($autoSave);
	}

	public function save()
	{		
		$content = '<?php $config = ' . var_export($this->values, true) . '; ?>';
	    $fullPath = $this->getFilePath();
	    if (!is_dir(dirname($fullPath)))
	    {
			mkdir(dirname($fullPath), 0777, true);
		}
		file_put_contents($fullPath, $content);
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

	public function getSectionTitle($sectionId)
	{
		$ini = parse_ini_file($this->getSectionFile($sectionId), true);	
		
		return key($ini);
	}

	public function getTree($dir = null, $keyPrefix = null)
	{
	  	if (!$dir)
	  	{
			$dir = ClassLoader::getRealPath('application.configuration.registry') . '/';
		}
		
		$res = array();
		$d = new DirectoryIterator($dir);
		
		$store = Store::getInstance();
		
		foreach ($d as $file)
		{
			if ($file->isFile() && 'ini' == substr($file->getFileName(), -3))
			{
				$ini = parse_ini_file($file->getPathName(), true);
				$key = substr($file->getFileName(), 0, -4);
				
				$out = array();
				$out['name'] = $store->translate(key($ini));
				
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

	public function getSettingsBySection($sectionId)
	{
		$section = $this->getSection($sectionId);		
		$store = Store::getInstance();

		$values = array();
		foreach ($section as $key => $value)
		{
			if ('-' == $value || '+' == $value)
			{
			  	$type = 'bool';
			  	$value = 1 - ('-' == $value);		  	
			}  
			elseif (is_numeric($value))
			{
			  	$type = 'num';
			}
			elseif ('/' == substr($value, 0, 1) && '/' == substr($value, -1))
			{
			  	$vl = explode(', ', substr($value, 1, -1));
			  	$type = array();
			  	foreach ($vl as $v)
			  	{
					$type[$v] = $store->translate($v);	
				}	
				
				$value = key($type);
			}
			else
			{
			  	$type = 'string';
			}
			
			$values[$key] = array('type' => $type, 'value' => $value, 'title' => $key);
		}		
		
		return $values;
	}

	public function setAutoSave($autoSave = true)
	{
		$this->autoSave = ($autoSave == true);
	}

	private function getAllSections($branch = null)
	{
		if (!$branch)
		{
			$branch = $this->getTree();	
		}
		
		$res = array();
		
		foreach ($branch as $key => $sub)
		{
			$res[] = $key;
			if (is_array($sub) && isset($sub['subs']))		
			{
				$res = array_merge($res, $this->getAllSections($sub['subs']));	
			}
		}
		
		return $res;
	}

	private function getSectionFile($sectionId)
	{
		return ClassLoader::getRealPath('application.configuration.registry.' . $sectionId) . '.ini';
	}

	private function getFilePath()
	{
	  	return ClassLoader::getRealPath('storage.configuration') . '/settings.php';
	}
	
}

?>