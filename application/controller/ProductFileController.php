<?php


/**
 *
 * @author Integry Systems
 * @package application.controller
 */
class ProductFileController extends FrontendController
{
	public function download()
	{
		$file = ProductFile::getInstanceByID($this->request->gget('id'), true);
		if ($file->isPublic->get())
		{
			return new ObjectFileResponse($file);
		}
	}
}

?>