<?php


/**
 *
 * @author Integry Systems
 * @package application/controller
 */
class ProductFileController extends FrontendController
{
	public function downloadAction()
	{
		$file = ProductFile::getInstanceByID($this->request->get('id'), true);
		if ($file->isPublic)
		{
			return new ObjectFileResponse($file);
		}
	}
}

?>