<?php

ClassLoader::import("application.controller.FrontendController");
ClassLoader::import('application.model.staticpage.StaticPage');
        
/**
 * Displays static pages
 *
 * @author Integry Systems
 * @package application.controller
 */
class StaticPageController extends FrontendController 
{
	public function view() 
	{
		$page = StaticPage::getInstanceByHandle($this->request->get('handle'));
		
        $response = new ActionResponse();
        $response->set('page', $page->toArray());		
		return $response;
	}
}

?>