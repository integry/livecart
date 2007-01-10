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
		return new ActionResponse();
	}
	
	public function categoryBox()
	{
	  
	}
}

?>