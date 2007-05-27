<?php

class LiveCartRenderer extends TemplateRenderer
{
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