<?php

class CssFile
{
	protected $relPath;

	protected $rules = null;

	public function __construct($relativePath)
	{
		$this->relPath = $relativePath;
	}

	public static function getInstanceFromUrl($url)
	{
		return new CssFile(self::getStyleSheetRelativePath($url));
	}

	public static function getInstanceFromPath($path)
	{
		return new CssFile(self::getStyleSheetRelativePath($path));
	}

	private function getStyleSheetRelativePath($url)
	{
		$path = parse_url($url, PHP_URL_PATH);
		return array_pop(explode('/public/', $path));
	}

	public function isPatched()
	{
		return file_exists($this->getPatchFile());
	}

	public function getPatchedFilePath()
	{
		$path = ClassLoader::getRealPath('public.upload.css.patched.') . md5($this->relPath) . '.css';
		return $path;
	}

	public function getPatchedFileRelativePath()
	{
		return 'upload/css/patched/' . md5($this->relPath) . '.css';
	}

	public function getPatchRules()
	{
		$file = $this->getPatchFile();

		if (!is_array($this->rules))
		{
			$this->rules = file_exists($file) ? include $file : array();
		}

		return $this->rules;
	}

	public function deleteSelector($selector)
	{
		$this->getPatchRules();
		$this->rules['selectors'][$selector] = true;
	}

	public function deleteProperty($selector, $property, $value = true)
	{
		$this->getPatchRules();
		$this->rules['properties'][$selector][$property] = $value;
	}

	public function save()
	{
		if ($this->rules)
		{
			$file = $this->getPatchFile();
			if (!file_exists(dirname($file)))
			{
				mkdir(dirname($file), 0777, true);
			}

			file_put_contents($file, '<?php return ' . var_export($this->rules, true) . '; ?>');
		}

		$patched = $orig = file_get_contents(ClassLoader::getRealPath('public.') . $this->relPath);

		if (isset($this->rules['selectors']))
		{
			foreach ($this->rules['selectors'] as $selector => $foo)
			{
				$patched = preg_replace('/' . $this->pregEscape($selector) . '.*\{.*\}/msU', '', $patched);
			}
		}

		if (isset($this->rules['properties']))
		{
			foreach ($this->rules['properties'] as $selector => $rules)
			{
				foreach ($rules as $rule => $value)
				{
					$patched = preg_replace('/' . $this->pregEscape($selector) . '(.*)\{(.*)\s{0,}' . $this->pregEscape($rule) . '[ ]{0,}:(.*)\n(.*)\}/msU', $selector . "\n{\\2\n\\4}", $patched);
				}
			}
		}

		file_put_contents($this->getPatchFile(), $patched);
	}

	private function pregEscape($string)
	{
		$res = preg_replace('/[^a-zA-Z0-9]/', "_|_\\0", $string);
		$res = str_replace('_|_', '\\', $res);

		return $res;
	}

	public function getPatchFile()
	{
		return ClassLoader::getRealPath('public.upload.css.delete.') . md5($this->relPath) . '.php';
	}

	public function clearPatchRules()
	{
		if (file_exists($this->getPatchFile()))
		{
			unlink($this->getPatchFile());
		}
	}
}

?>