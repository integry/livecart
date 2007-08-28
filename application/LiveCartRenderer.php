<?php

ClassLoader::import('framework.renderer.SmartyRenderer');
ClassLoader::import('application.LiveCartSmarty');

class LiveCartRenderer extends SmartyRenderer
{    	
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

	/**
	 * Process
	 *
	 * @param Renderable $object Object to render
	 * @param string $view Path to view
	 * @return string Rendered output
	 * @throws ViewNotFoundException if view does not exists
	 */
	public function process(Renderable $object, $view)
	{
		$customizedPath = ClassLoader::getRealPath('storage.customize.view.') . $view;

		if (file_exists($customizedPath))
		{
			$view = $customizedPath;
		}
				
		return parent::process($object, $view);
	}	
}

?>