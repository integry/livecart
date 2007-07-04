<?php

ClassLoader::import("application.controller.FrontendController");

/**
 * Index controller for frontend
 *
 * @package application.controller
 */
class IndexController extends FrontendController 
{
	public function index() 
	{
//		return new ActionResponse();
        ClassLoader::import('application.controller.CategoryController');
		
		$this->request->set('id', Category::ROOT_ID);
		$this->request->set('cathandle', '.');
		
        $controller = new CategoryController($this->request);		
		$response = $controller->index();
		
		return $response;
	}

}

?>