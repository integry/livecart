<?php

ClassLoader::import('framework.renderer.SmartyRenderer');
ClassLoader::import('application.LiveCartSmarty');

/**
 *  Implements LiveCart-specific view renderer logic
 *
 *  @package application
 *  @author Integry Systems
 */
class LiveCartRenderer extends SmartyRenderer
{
	private $paths = array();

	/**
	 * Template renderer constructor
	 *
	 * Creates a smarty instance and sets a compile directory path (this is required
	 * by smarty)
	 */
	public function __construct(LiveCart $application)
	{
		self::registerHelperDirectory(ClassLoader::getRealPath('application.helper.smarty'));
		self::registerHelperDirectory(ClassLoader::getRealPath('application.helper.smarty.form'));
		parent::__construct($application);
	}

	/**
	 * Gets a smarty instance
	 *
	 * @return Smarty
	 */
	public function getSmartyInstance()
	{
		if (!$this->tpl)
		{
			$this->tpl = new LiveCartSmarty(self::getApplication());
			$this->tpl->compile_dir = self::$compileDir;
			$this->tpl->template_dir = ClassLoader::getRealPath("application.view");
		}

		return $this->tpl;
	}

	public function getTemplatePaths($template = '')
	{
		if (!$this->paths)
		{
			if ($theme = self::getApplication()->getTheme())
			{
				$this->paths[] = ClassLoader::getRealPath('storage.customize.view.theme.' . $theme . '.');
				$this->paths[] = ClassLoader::getRealPath('application.view.theme.' . $theme . '.');
			}

			$this->paths[] = ClassLoader::getRealPath('storage.customize.view.');
			$this->paths[] = ClassLoader::getRealPath('application.view.');

		}

		if (!$template)
		{
			return $this->paths;
		}

		$paths = $this->paths;
		foreach ($paths as &$path)
		{
			$path = $path . $template;
		}

		return $paths;
	}

	public function getTemplatePath($template)
	{
		foreach ($this->getTemplatePaths($template) as $path)
		{
			if (is_readable($path))
			{
				return $path;
			}
		}
	}
}

?>