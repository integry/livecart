<?php

namespace system;

/**
 * System configuration manager
 *
 * @package application/model/system
 * @author Integry Systems <http://integry.com>
 */
class Config
{
	/**
	 *  Configuration value array (key => value)
	 */
	private $values = array();
	private $runTimeValues = array();

	private $autoSave = false;

	private $isUpdated = false;

	private $di;

	private $directories = array();

	public function __construct(\Phalcon\DI\FactoryDefault $di)
	{
		$filePath = $this->getFilePath();
		if (file_exists($filePath) || file_exists($this->getControlFilePath()))
		{
			// avoid race conditions when reading a config file that is being written at the same time
			$time = time() + 1;
			while (!is_readable($filePath) || (time() > $time)) { sleep(1); }

			if (!include $filePath)
			{
				die('Cannot read configuration file');
			}

			$this->values = $config;
		}

		$this->di = $di;
	}
	
	public function setAll($values)
	{
		$this->values = $values;
	}

	public function has($key, $updateIfNotFound = false)
	{
		if (!isset($this->values[$key]) && !$this->isUpdated && $updateIfNotFound)
		{
		  	//$this->updateSettings();
		}

		return isset($this->values[$key]);
	}

	public function get($key)
	{
		if (isset($this->runTimeValues[$key]))
		{
			return $this->runTimeValues[$key];
		}

		if (!isset($this->values[$key]) && !$this->isUpdated)
		{
		  	$this->updateSettings();
		}

		if (isset($this->values[$key]))
		{
		  	if (is_array($this->values[$key]))
		  	{
				$app = $this->di->get('application');
				$lang = $app->getLocaleCode();
				if (!empty($this->values[$key][$lang]))
				{
					return $this->values[$key][$lang];
				}
				else if (isset($this->values[$key][$app->getDefaultLanguageCode()]))
				{
					return $this->values[$key][$app->getDefaultLanguageCode()];
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
			return null;
		}
	}

	public function toArray()
	{
		$array = array();

		// some of the values are multi-language, so we can't just return the values array
		// @todo - this can obviously be optimized
		foreach ($this->values as $key => $value)
		{
			if ($this->has($key))
			{
				$array[$key] = $this->get($key);
			}
		}

		return $array;
	}

	public function set($key, $value)
	{
		if (is_numeric($value) && ($value < 100000) /* do not touch very large values */)
		{
			$value = (float)$value;
		}

		$this->values[$key] = $value;
		$this->runTimeValues[$key] = $value;

		if ($this->autoSave)
		{
			$this->save();
		}
	}

	public function setRuntime($key, $value)
	{
		$this->runTimeValues[$key] = $value;
	}

	public function resetRuntime($key)
	{
		unset($this->runTimeValues[$key]);
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
				if (strlen($k) != 2 || !preg_match('/^[a-z]{2}$/', $k))
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
		return;
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
		if (!$this->values)
		{
			return;
		}
		
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

			if (!file_exists($this->getControlFilePath()))
			{
				file_put_contents($this->getControlFilePath(), '');
			}
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
			$tree = array();

			foreach ($this->getDirectories() as $directory)
			{
				$tree = array_merge($tree, $this->getTree($directory));
			}

			return $tree;
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
				$out['title'] = $this->application->translate(key($ini));

				$subpath = $file->getPath() . '/' . substr($key, 3);

				if (file_exists($subpath) && (strlen($key) > 3))
				{
				  	$out['children'] = $this->getTree($subpath, substr($key, 3));
				}

				if ($keyPrefix)
				{
				  	$key = $keyPrefix . '.' . $key;
				}

				$out['id'] = $key;

				$res[] = $out;
			}
		}

		usort($res, function ($a, $b) { return $a['id'] > $b['id'] ? 1 : -1; });

		return $res;
	}

	public function getSectionList()
	{
		function getBranchSections($branch)
		{
			$sections = array();
			foreach ($branch['children'] as $element)
			{
				$sections[] = $element['id'];
				if (!empty($element['children']))
				{
					$sections = array_merge($sections, getBranchSections($element));
				}
			}

			return $sections;
		}

		return getBranchSections(array('children' => $this->getTree()));
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
			elseif (is_array($value) || ('/' == substr($value, 0, 1)) || ('+/' == substr($value, 0, 2)) && '/' == substr($value, -1))
			{
			  	if (!is_array($value))
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
					$type = $value['values'];

					if (isset($value['default']))
					{
						$value = $value['default'];
					}
					else
					{
						$value = key($type);
					}
				}
			}
			else if ('@' == substr($value, 0, 1))
			{
				$type = 'longtext';
				$value = substr($value, 1);
			}
			else if ('%' == substr($value, 0, 1))
			{
				$type = 'image';
				$value = substr($value, 1);
			}
			elseif (is_numeric($value))
			{
			  	$type = (strpos($value, '.') !== false) ? 'float' : 'num';
			}
			else
			{
			  	$type = 'string';
			}

			$values[$key] = array('type' => $type, 'value' => $value, 'title' => $key, 'extra' => $extra, 'section' => $sectionId);
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
			if (is_array($sub) && isset($sub['children']))
			{
				$res = array_merge($res, $this->getAllSections($sub['children']));
			}
		}

		return $res;
	}

	private function getSectionFile($sectionId)
	{
		foreach ($this->getDirectories() as $dir)
		{
			$path = $dir . '/' . str_replace('.', '/', $sectionId) . '.ini';
			if (file_exists($path))
			{
				return $path;
			}
		}
	}

	private function getDirectories()
	{
		if (!$this->directories)
		{
			//$this->directories = $this->application->getConfigContainer()->getConfigDirectories();
		}

		return $this->directories;
	}

	private function getFilePath()
	{
		return $this->getPath('storage/configuration/') . 'settings.php';
	}

	private function getControlFilePath()
	{
		return $this->getPath('cache/') . 'configExists.php';
	}

	public function getValues()
	{
		return $this->values;
	}

	public function getPath($path)
	{
		return __ROOT__ . $path;
	}
}

?>
