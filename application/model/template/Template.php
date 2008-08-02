<?php

/**
 * Page template file logic - saving and retrieving template code.
 *
 * There are two sets of template files active at the same time:
 *
 *		1) application.view - default view template files
 *		2) storage.customize - edited template files.
 *
 * This system allows to modify template files without overwriting the existing ones, among other benefits.
 *
 * @package application.model.template
 * @author Integry Systems <http://integry.com>
 */
class Template
{
	protected $code;

	protected $file;

	protected $theme;

	public function __construct($fileName, $theme = null)
	{
		$this->theme = $theme;

		// do not allow to leave view template directory by prefixing ../
		$fileName = preg_replace('/^[\\\.\/]+/', '', $fileName);

		if ($this->theme)
		{
			if (substr($fileName, 0, 6) == 'theme/')
			{
				$parts = explode('/', $fileName, 3);
				$fileName = $parts[2];
			}

			$fileName = 'theme/' . $this->theme . '/' . $fileName;
		}

		$path = self::getRealFilePath($fileName);

		if (file_exists($path))
		{
			$this->code = file_get_contents($path);
		}

		$this->file = $fileName;
	}

	public static function getTree($dir = null, $isCustom = null)
	{
	  	if (!$dir)
	  	{
			$dir = ClassLoader::getRealPath('application.view.');

			// get user created template files
			$customFiles = self::getTree(ClassLoader::getRealPath('storage.customize.view.'), true);
		}

		if (!file_exists($dir))
		{
			return array();
		}

		$rootLn = strlen(ClassLoader::getRealPath($isCustom ? 'storage.customize.view.' : 'application.view.'));

		$res = array();
		$d = new DirectoryIterator($dir);

		foreach ($d as $file)
		{
			if (!$file->isDot())
			{
				$id = substr($file->getPathName(), $rootLn);

				if ($file->isDir())
				{
					$dir = self::getTree($file->getPathName(), $isCustom);
					if ($dir)
					{
						$res[$file->getFileName()]['id'] = $id;
						$res[$file->getFileName()]['subs'] = $dir;
					}
				}
				else //if (substr($file->getFileName(), -4) == '.tpl')
				{
					$res[$file->getFileName()]['id'] = $id;
					$res[$file->getFileName()]['isCustom'] = !file_exists(self::getOriginalFilePath($id));
				}
			}
		}

		if (isset($customFiles))
		{
			$res = self::array_merge_rec($res, $customFiles);
		}

		uasort($res, array('Template', 'sortTree'));

		return $res;
	}

	public static function getRealFilePath($fileName)
	{
		$paths = array();
		$paths[] = self::getCustomizedFilePath($fileName);
		$paths[] = self::getOriginalFilePath($fileName);

		foreach ($paths as $path)
		{
			if (file_exists($path))
			{
				return $path;
			}
		}
	}

	public static function getOriginalFilePath($fileName)
	{
		return ClassLoader::getRealPath('application.view.') . $fileName;
	}

	public static function getCustomizedFilePath($fileName)
	{
		return ClassLoader::getRealPath('storage.customize.view.') . $fileName;
	}

	public function setCode($code)
	{
		$this->code = $code;
	}

	public function getCode()
	{
	 	return $this->code;
	}

	public function getFileName()
	{
		return $this->file;
	}

	public function sortTree($a, $b)
	{
		$ap = (isset($a['subs']) * 10) + strnatcasecmp($b['id'], $a['id']);
		$bp = (isset($b['subs']) * 10) + strnatcasecmp($a['id'], $b['id']);

		return $ap > $bp ? -1 : ($ap == $bp) ? 0 : 1;
	}

	private function checkForChanges()
	{
		$l = str_replace("\r\n", "\n", $this->getContent(self::getCustomizedFilePath($this->file)));
		$r = str_replace("\r\n", "\n", $this->getContent(self::getOriginalFilePath($this->file)));

		if ($l == $r)
		{
			$this->restoreOriginal();
		}
	}

	private function getContent($file)
	{
		if (file_exists($file))
		{
			return file_get_contents($file);
		}
	}

	public function save()
	{
		$path = self::getCustomizedFilePath($this->file);

		$dir = dirname($path);
		if (!is_dir($dir))
		{
			mkdir($dir, 0777, true);
			chmod($dir, 0777);
		}

		$res = file_put_contents($path, $this->code);

		$this->checkForChanges();

		return $res !== false;
	}

	public function restoreOriginal()
	{
		$path = self::getCustomizedFilePath($this->file);
		if (!file_exists($path))
		{
			return true;
		}

		$cacheDir = ClassLoader::getRealPath('cache.templates_c.customize');
		if (is_dir($cacheDir))
		{
			foreach (new DirectoryIterator($cacheDir) as $file)
			{
				if (!$file->isDot())
				{
					unlink($file->getPathname());
				}
			}
		}

		return unlink($path);
	}

	public function isCustomFile()
	{
		return !file_exists(self::getOriginalFilePath($this->file));
	}

	public function toArray()
	{
		$array = array();
		$array['code'] = $this->code;
		$array['file'] = $this->file;
		$array['isCustomized'] = file_exists(self::getCustomizedFilePath($this->file));
		$array['isCustomFile'] = $this->isCustomFile();
		return $array;
	}

	private function array_merge_rec($array1, $array2)
	{
		$arrays = func_get_args();
		$narrays = count($arrays);

		// check arguments
		// comment out if more performance is necessary (in this case the foreach loop will trigger a warning if the argument is not an array)
		for ($i = 0; $i < $narrays; $i ++) {
			if (!is_array($arrays[$i])) {
				// also array_merge_recursive returns nothing in this case
				trigger_error('Argument #' . ($i+1) . ' is not an array - trying to merge array with scalar! Returning null!', E_USER_WARNING);
				return;
			}
		}

		// the first array is in the output set in every case
		$ret = $arrays[0];

		// merege $ret with the remaining arrays
		for ($i = 1; $i < $narrays; $i ++) {
			foreach ($arrays[$i] as $key => $value) {
				if (((string) $key) === ((string) intval($key))) { // integer or string as integer key - append
					$ret[] = $value;
				}
				else { // string key - megre
					if (is_array($value) && isset($ret[$key])) {
						// if $ret[$key] is not an array you try to merge an scalar value with an array - the result is not defined (incompatible arrays)
						// in this case the call will trigger an E_USER_WARNING and the $ret[$key] will be null.
						$ret[$key] = self::array_merge_rec($ret[$key], $value);
					}
					else {
						$ret[$key] = $value;
					}
				}
			}
		}

		return $ret;
}
}

?>
