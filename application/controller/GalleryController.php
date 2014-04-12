<?php

/**
 *
 * @author Integry Systems
 * @package application/controller
 */
class GalleryController extends FrontendController
{
	public function indexAction()
	{
		$this->set('galleries', Gallery::query()->orderBy('position')->execute());
	}

	public function galleryAction($id)
	{
		$gallery = Gallery::getInstanceByID($id);
		$this->set('gallery', $gallery);
		$this->set('imageArray', $gallery->galleryImages->toArray());
	}
	
	public function imageAction()
	{
		
	}
}

?>
