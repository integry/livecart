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
	
	private $isUpdated = false;

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

	public function isValueSet($key)
	{
		if (!isset($this->values[$key]) && !$this->isUpdated)
		{
		  	$this->updateSettings();
		}
        
        return isset($this->values[$key]);
    }
	
	public function getValue($key)
	{
		if (!isset($this->values[$key]) && !$this->isUpdated)
		{
		  	$this->updateSettings();
		}
		
		if (isset($this->values[$key]))
		{
		  	if (is_array($this->values[$key]))
		  	{
                $lang = Store::getInstance()->getLocaleCode();
                if (!empty($this->values[$key][$lang]))       
                {
                    return $this->values[$key][$lang];
                }
                else if (isset($this->values[$key][Store::getInstance()->getDefaultLanguageCode()]))
                {
                    return $this->values[$key][Store::getInstance()->getDefaultLanguageCode()];
                }
                else
                {
                    return $this->values[$key];
                }                
            }
            else
            {
                return $this->values[$key];
            }
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

	public function setValueByLang($key, $lang, $value)
	{
		if (!is_array($this->values[$key]))
		{
            $this->values[$key] = array();    
        }
        
        $this->values[$key][$lang] = $value;
		
		if ($this->autoSave)
		{
			$this->save();
		}
	}

	public function getValueByLang($key, $lang)
	{
        if (isset($this->values[$key][$lang]))
        {
            return $this->values[$key][$lang];
        }        
    }

    public function isMultiLingual($key)
    {
        return is_array($this->values[$key]);
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
					// check for multi-lingual values
                    if (!is_array($value['value']) && substr($value['value'], 0, 1) == '_')
					{
                        $value['value'] = array(Store::getInstance()->getDefaultLanguageCode() => substr($value['value'], 1)); 
                    }
                    
                    $this->values[$key] = $value['value'];
				}	
			}
		}
		
		$this->save();
		$this->setAutoSave($autoSave);
		
		$this->isUpdated = true;		
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
			$extra = '';
			
            // evaluate PHP code
            if (substr($value, 0, 5) == '<?php')
            {
				$value = substr($value, 5);
				if (substr($value, -2) == '?>')
				{
					$value = substr($value, 0, -2);
				}
				
				eval('$value = ' . $value . ';');			
			}
			
			if ('-' == $value || '+' == $value)
			{
			  	$type = 'bool';
			  	$value = 1 - ('-' == $value);		  	
			}  
			elseif (is_numeric($value))
			{
			  	$type = 'num';
			}
			elseif (('/' == substr($value, 0, 1)) || ('+/' == substr($value, 0, 2)) && '/' == substr($value, -1))
			{
			  	if (substr($value, 0, 1) == '+')
			  	{
                    $extra = 'multi';
                    $value = substr($value, 1);
                    $multivalues = array();
                }
                  
                $vl = explode(', ', substr($value, 1, -1));
			  	$type = array();
			  	foreach ($vl as $v)
			  	{
					if ('multi' == $extra)
					{
                        if (substr($v, 0, 1) == '+')
                        {
                            $v = substr($v, 1);
                            $multivalues[$v] = 1;
                        }                        
                    }
                    
                    $type[$v] = $store->translate($v);	
				}	
				
                $value = key($type);
                
                if ('multi' == $extra)
                {
                    $value = $multivalues;    
                }                
			}
			else
			{
			  	$type = 'string';
			}
			
			$values[$key] = array('type' => $type, 'value' => $value, 'title' => $key, 'extra' => $extra);
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