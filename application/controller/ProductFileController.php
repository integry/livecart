<?php

ClassLoader::import('application.model.product.ProductFile');
ClassLoader::import('application.controller.FrontendController');

/**
 *
 * @author Integry Systems
 * @package application.controller
 */
class ProductFileController extends FrontendController
{
	public function download()
	{
		$file = ProductFile::getInstanceByID($this->request->get('id'), true);
		if ($file->isPublic->get())
		{
			return new ObjectFileResponse($file);
		}
	}
}

?>