<?php

ClassLoader::import("application.controller.FrontendController");

class ErrorController extends FrontendController
{
	function index()
	{
	   print_r(User::getCurrentUser()->toArray());
       echo 'error ' . $this->request->getValue('id');
	//	return new ActionResponse();
	}	
}

?>