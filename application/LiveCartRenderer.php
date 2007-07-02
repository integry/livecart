<?php

class LiveCartRenderer extends TemplateRenderer
{
	/**
	 * Template renderer constructor
	 *
	 * Creates a smarty instance and sets a compile directory path (this is required
	 * by smarty)
	 */
	public function __construct(Router $router)
	{
		self::registerHelperDirectory(ClassLoader::getRealPath('application.helper'));
		parent::__construct($router);		
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