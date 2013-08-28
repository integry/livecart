<?php

class LiveVolt extends \Phalcon\Mvc\View\Engine\Volt
{
	protected $paths = array();
	protected $cachedTemplatePath = array();
	protected $globals = array();

	public function getCompiler()
	{
		if (empty($this->_compiler))
		{
			$this->_compiler = new LiveVoltCompiler($this->getView());
			$this->_compiler->setOptions($this->getOptions());
			$this->_compiler->setDI($this->getDI());

			$this->_compiler->addFunction('config', function($resolvedArgs, $exprArgs) {
				return '$this->getDI()->get(\'config\')->get(' . $resolvedArgs . ')';
			});

			$this->_compiler->addFunction('global', function($resolvedArgs, $exprArgs) {
				return '$this->setOrReturnGlobal(' . $resolvedArgs . ')';
			});
		}

		return $this->_compiler;
	}

	public function render($templatePath, $params, $mustClean = null)
	{
		$path = $this->getTemplatePath($this->getRelativeTemplatePath($templatePath));

		return parent::render($path ? $path : $templatePath, $params, $mustClean);
	}

	public function partial($partialPath)
	{
		return parent::render($this->getTemplatePath($partialPath), $this->globals);
	}

	public function setOrReturnGlobal($key, $value = null)
	{
		if ($value)
		{
			$this->globals[$key] = $value;
		}
		else
		{
			return isset($this->globals[$key]) ? $this->globals[$key] : '';
		}
	}

	public function getGlobals()
	{
		return $this->globals;
	}

	public function getTemplatePaths($template = '')
	{
		if (!$this->paths)
		{
			if ($theme = $this->application->getTheme())
			{
				$this->paths = array_merge($this->paths, $this->getThemePaths($theme));
			}

			$this->paths[] = $this->config->getPath('storage/customize/view/');
			$this->paths[] = $this->config->getPath('application/view/');
			$this->paths[] = dirname($this->config->getPath('module'));
		}

		if (!$template)
		{
			return $this->paths;
		}

		$paths = $this->paths;

		foreach ($paths as &$path)
		{
			$path = $this->getPath($path, $template);
		}

		return $paths;
	}

	public function resetPaths()
	{
		$this->paths = array();
	}

	public function getTemplatePath($template)
	{
		if (!isset($this->cachedTemplatePath[$template]))
		{
			foreach ($this->getTemplatePaths($template) as $path)
			{
				if (is_readable($path))
				{
					$this->cachedTemplatePath[$template] = $path;
					break;
				}
			}

			if (!isset($this->cachedTemplatePath[$template]))
			{
				$this->cachedTemplatePath[$template] = null;
			}
		}

		return $this->cachedTemplatePath[$template];
	}

	public function getBaseTemplatePath($tplName)
	{
		$tplName = substr($tplName, 1);
		foreach (array($this->config->getPath('storage/customize/view/'), $this->config->getPath('application/view/')) as $path)
		{
			$file = $path . $tplName;
			if (file_exists($file))
			{
				return $file;
			}
		}
	}

	public function getRelativeTemplatePath($template)
	{
		$template = str_replace('\\', '/', $template);
		if (preg_match('/\/(module\/.*\/.*)/', $template, $match))
		{
			preg_match('/\/(module\/.*)/', $template, $match);
			return str_replace('application/view/', '', $match[1]);
		}

		foreach (array('application/view', 'storage/customize/view') as $path)
		{
			$path = $this->config->getPath($path);
			$path = str_replace('\\', '/', $path);

			if (substr($template, 0, strlen($path)) == $path)
			{
				return substr($template, strlen($path) + 1);
			}
		}
	}

	private function getPath($root, $template)
	{
		if (substr($template, 0, 7) == 'module/')
		{
			if ($this->paths[count($this->paths) - 1] == $root)
			{
				$root = dirname($this->config->getPath('module'));
				$template = preg_replace('/module\/([-a-zA-Z0-9_]+)\/(.*)/', 'module/\\1/application/view/\\2', $template);
			}
		}

		return $root . '/' . $template;
	}

	private function getThemePaths($theme, $includedThemes = array())
	{
		$paths = $inheritConf = array();
		$paths[] = $this->config->getPath('storage/customize/view/theme.' . $theme . '.');
		$paths[] = $this->config->getPath('application/view/theme.' . $theme . '.');

		$inheritConf[] = $this->config->getPath('storage/customize/view/theme/' . $theme . '/inherit') . '.php';
		$inheritConf[] = $this->config->getPath('application/view/theme/' . $theme . '/inherit') . '.php';

		foreach ($inheritConf as $inherit)
		{
			if (file_exists($inherit))
			{
				break;
			}
		}

		if (file_exists($inherit))
		{
			$inherited = include $inherit;
			if (!is_array($inherited))
			{
				$inherited = array($inherited);
			}

			foreach ($inherited as $parent)
			{
				if (empty($includedThemes[$parent]))
				{
					$includedThemes[$parent] = true;
					$paths = array_merge($paths, $this->getThemePaths($parent, $includedThemes));
				}
			}
		}

		return $paths;
	}
}

class LiveVoltCompiler extends \Phalcon\Mvc\View\Engine\Volt\Compiler
{
	protected function _compileSource($source, $something = null)
	{
		$source = str_replace('{{', '<' . '?php $ng = <<<NG' . "\n" . '\x7B\x7B', $source);
		$source = str_replace('}}', '\x7D\x7D' . "\n" . 'NG;' . "\n" . ' echo $ng; ?' . '>', $source);

		$source = str_replace('[[', '{{', $source);
		$source = str_replace(']]', '}}', $source);

		//$source = '<' . '?php extract($this->getGlobals()); ?' . '>' . $source;

		return parent::_compileSource($source, $something);
	}
}