<?php

ClassLoader::import("application.controller.FrontendController");
ClassLoader::import('application.model.staticpage.StaticPage');
        
/**
 * Displays static pages
 *
 * @package application.controller
 */
class StaticPageController extends FrontendController 
{
	public function view() 
	{
		$page = StaticPage::getInstanceByHandle($this->request->getValue('handle'));
		
        $response = new ActionResponse();
        $response->setValue('page', $page->toArray());		
		return $response;
	}
}

?>