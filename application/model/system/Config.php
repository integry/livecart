<?php

/**
 * System configuration manager
 *
 * @package application.model.system
 * @author Integry Systems <http://integry.com>
 */
class Config
{
	/**
	 *  Configuration value array (key => value)
	 */
	private $values = array();

	private $autoSave = true;

	private $isUpdated = false;

	private $application;

	public function __construct(LiveCart $application)
	{
		$filePath = $this->getFilePath();
		if (file_exists($filePath))
		{
			include $filePath;
			$this->values = $config;
		}

		$this->application = $application;
	}

	public function isValueSet($key, $updateIfNotFound = false)
	{
		if (!isset($this->values[$key]) && !$this->isUpdated && $updateIfNotFound)
		{
		  	$this->updateSettings();
		}

		return isset($this->values[$key]);
	}

	public function get($key)
	{
		if (!isset($this->values[$key]) && !$this->isUpdated)
		{
		  	$this->updateSettings();
		}

		if (isset($this->values[$key]))
		{
		  	if (is_array($this->values[$key]))
		  	{
				$lang = $this->application->getLocaleCode();
				if (!empty($this->values[$key][$lang]))
				{
					return $this->values[$key][$lang];
				}
				else if (isset($this->values[$key][$this->application->getDefaultLanguageCode()]))
				{
					return $this->values[$key][$this->application->getDefaultLanguageCode()];
				}
				else if ($this->isMultiLingual($key))
				{
					reset($this->values[$key]);
					return $this->values[$key][key($this->values[$key])];
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

	public function toArray()
	{
		$array = array();

		// some of the values are multi-language, so we can't just return the values array
		// @todo - this can obviously be optimized
		foreach ($this->values as $key => $value)
		{
			$array[$key] = $this->get($key);
		}

		return $array;
	}

	public function set($key, $value)
	{
		if (is_numeric($value))
		{
			$value = (float)$value;
		}

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
		if (is_array($this->values[$key]))
		{
			foreach($this->values[$key] as $k => $v)
			{
				if (strlen($k) != 2 || !is_string($v) || !preg_match('/^[a-z]{2}$/', $k))
				{
					return false;
				}
			}

			return count($this->values[$key]) > 0;
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
			try
			{
				$s = $this->getSettingsBySection($section);

				foreach ($s as $key => $value)
				{
					if (!isset($this->values[$key]))
					{
						// check for multi-lingual values
						if ((!is_array($value['value']) && substr($value['value'], 0, 1) == '_') || 'longtext' == $value['type'])
						{
							$value['value'] = array($this->application->getDefaultLanguageCode() => (string)substr($value['value'], 'longtext' != $value['type']));
						}

						$this->set($key, $value['value']);
					}
				}
			}
			catch (SQLException $e)
			{
				// not installed yet
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

		$directoryExists = true;
		if (!is_dir(dirname($fullPath)))
		{
			$directoryExists = @mkdir(dirname($fullPath), 0777, true);
		}

		if ($directoryExists)
		{
			file_put_contents($fullPath, $content);
		}
	}

	public function getSection($sectionId)
	{
		$file = $this->getSectionFile($sectionId);
		if (file_exists($file))
		{
			return parse_ini_file($file);
		}
		else
		{
			return array();
		}
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

		// remove validation rules
		foreach ($ini as &$sect)
		{
			foreach ($sect as $key => $value)
			{
				if (substr($key, 0, 9) == 'validate_')
				{
					unset($sect[$key]);
				}
			}
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

		foreach ($d as $file)
		{
			if ($file->isFile() && 'ini' == substr($file->getFileName(), -3))
			{

				$ini = parse_ini_file($file->getPathName(), true);
				$key = substr($file->getFileName(), 0, -4);

				$out = array();
				$out['name'] = $this->application->translate(key($ini));

				$subpath = $file->getPath() . '/' . substr($key, 3);

				if (file_exists($subpath) && (strlen($key) > 3))
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

		ksort($res);

		return $res;
	}

	public function getSettingsBySection($sectionId)
	{
		$section = $this->getSection($sectionId);

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
			else if ('@' == substr($value, 0, 1))
			{
				$type = 'longtext';
				$value = substr($value, 1);
			}
			elseif (is_numeric($value))
			{
			  	$type = (strpos($value, '.') !== false) ? 'float' : 'num';
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

					$type[$v] = $this->application->translate($v);
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
