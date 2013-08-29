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

		$this->theme = self::getTheme($theme);
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

	public static function getTheme($theme)
	{
		return $theme ? $theme : 'barebone';
	}

	public static function getInstanceFromUrl($url, $theme = null)
	{
		return new CssFile(self::getStyleSheetRelativePath($url), self::getTheme($theme));
	}

	public static function getInstanceFromPath($path, $theme = null)
	{
		return new CssFile(self::getStyleSheetRelativePath($path), self::getTheme($theme));
	}

	private function getStyleSheetRelativePath($url)
	{
		$path = parse_url($url, PHP_URL_PATH);
		return array_pop(explode('/public/', $path));
	}

	public function isPatched()
	{
		$isPatched = file_exists($this->getPatchFile());

		if ($isPatched && !file_exists($this->getPatchedFilePath()))
		{
			$this->getPatchRules();
			$this->save();
		}

		return $isPatched;
	}

	public function getPatchedFilePath()
	{
		return ClassLoader::getRealPath('public/upload/css/patched/') . $this->theme . '-' . md5($this->relPath) . '.css';
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
			$this->saveFile(ClassLoader::getRealPath('public/') . $this->relPath, $this->source);
			return true;
		}

		if (preg_match('/upload\/css\//', $this->relPath))
		{
			return true;
		}

		if ($this->rules)
		{
			$this->rules['file'] = $this->relPath;
			$this->saveFile($this->getPatchFile(), '<?php return ' . var_export($this->rules, true) . '; ?>');
		}

		$patched = $orig = file_get_contents(ClassLoader::getRealPath('public/') . $this->relPath);

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
					$preg = '/' . $this->pregEscape($selector) . '\s*\{([^\{\}]*)\s*' . $this->pregEscape($rule) . '\s*:\s*([^\n]+)[\n|\}]{1,}([^\{\}]*)\}/msU';
					$patched = preg_replace($preg, $selector . "\n{\\1\n\\3}", $patched, 1);
				}
			}
		}

		// background image paths
		$relPath = $this->relPath;
		$patched = str_replace('url(..', 'url(../../../' . dirname($relPath) . '/..', $patched);
		$patched = str_replace('url(\'..', 'url(\'' . dirname($relPath) . '/..', $patched);
		$patched = str_replace('url(\"..', 'url(\'' . dirname($relPath) . '/..', $patched);
		$patched = preg_replace('/url\(([a-z])/', 'url(../../../' . dirname($relPath) . '/\1', $patched);

		$this->saveFile($this->getPatchedFilePath(), $patched);
	}

	private function pregEscape($string)
	{
		// characters to escape for preg_match
		$res = preg_replace('/[^ ,a-zA-Z0-9]/', "_|_\\0", $string);

		// Firebug removes the newlines for multi-line selectors
		$res = str_replace(', ', ',\s{0,}', $res);

		// err... how do you put a backslash in the replace :/
		$res = str_replace('_|_', '\\', $res);

		return $res;
	}

	public function saveFile($file, $contents)
	{
		if (!file_exists(dirname($file)))
		{
			mkdir(dirname($file), 0777, true);
		}

		// IE7 fixes
		$contents = str_replace('display: inline-block;', 'display: inline-block; zoom: 1; *display: inline;', $contents);
		$contents = str_replace('display: table-cell;', 'display: table-cell; zoom: 1;', $contents);

		file_put_contents($file, $contents);
	}

	public function getPatchFile($patchedName = null)
	{
		return ClassLoader::getRealPath('public/upload/css/delete/') . ($patchedName ? $patchedName : $this->theme . '-' . md5($this->relPath)) . '.php';
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