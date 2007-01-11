<?php

ClassLoader::import("application.controller.FrontendController");

/**
 * Index controller for frontend
 *
 * @package application.controller
 */
class CategoryController extends FrontendController 
{
	public function index() 
	{
		$this->categoryID = $this->request->getValue('id');
		
		$response = new ActionResponse();
		$response->setValue('id', $this->categoryID);
		return $response;
	}	
}

?>