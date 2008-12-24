<?php

class CssFile
{
	protected $relPath;

	protected $rules = null;

	protected $source = null;

	protected $theme;

	public function __construct($relativePath, $theme = null)
	{
		if (preg_match('/^upload\/css\/patched\//', $relativePath))
		{
			$data = include self::getPatchFile(basename($relativePath, '.css'));
			$relativePath = $data['file'];
		}

		$this->relPath = $relativePath;
		$this->theme = $theme;
	}

	public function setSource($source)
	{
		// remove empty selectors
		$source = preg_replace('/[^\n]{1,}[\n]{0,}\{\s{0,}\}/msU', '', $source);

		$this->source = $source;
	}

	public function getSource()
	{
		return $this->source;
	}

	public static function getInstanceFromUrl($url, $theme = null)
	{
		return new CssFile(self::getStyleSheetRelativePath($url), $theme);
	}

	public static function getInstanceFromPath($path, $theme = null)
	{
		return new CssFile(self::getStyleSheetRelativePath($path), $theme);
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
		return ClassLoader::getRealPath('public.upload.css.patched.') . $this->theme . '-' . md5($this->relPath) . '.css';
	}

	public function getPatchedFileRelativePath()
	{
		return 'upload/css/patched/' . $this->theme . '-' . md5($this->relPath) . '.css';
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
		if (!is_null($this->source))
		{
			$this->saveFile(ClassLoader::getRealPath('public.') . $this->relPath, $this->source);
			return true;
		}

		if ($this->rules)
		{
			$this->rules['file'] = $this->relPath;
			$this->saveFile($this->getPatchFile(), '<?php return ' . var_export($this->rules, true) . '; ?>');
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

		// background image paths
		$relPath = $this->relPath;
		$patched = str_replace('url(..', 'url(../../../' . dirname($relPath) . '/..', $patched);
		$patched = str_replace('url(\'..', 'url(\'' . dirname($relPath) . '/..', $patched);
		$patched = str_replace('url(\"..', 'url(\'' . dirname($relPath) . '/..', $patched);

		$this->saveFile($this->getPatchedFilePath(), $patched);
	}

	private function pregEscape($string)
	{
		$res = preg_replace('/[^a-zA-Z0-9]/', "_|_\\0", $string);
		$res = str_replace('_|_', '\\', $res);

		return $res;
	}

	public function saveFile($file, $contents)
	{
		if (!file_exists(dirname($file)))
		{
			mkdir(dirname($file), 0777, true);
		}

		file_put_contents($file, $contents);
	}

	public function getPatchFile($patchedName = null)
	{
		return ClassLoader::getRealPath('public.upload.css.delete.') . ($patchedName ? $patchedName : $this->theme . '-' . md5($this->relPath)) . '.php';
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