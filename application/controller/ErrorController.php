<?php

ClassLoader::import("application.controller.FrontendController");

class ErrorController extends FrontendController
{
	function index()
	{
		$id = $this->request->getValue('id');
		switch($id)
		{
		    case 401:
		        return new ActionRedirectResponse('user', 'login');
		    case 403:
		        return new ActionRedirectResponse('index', 'forbidden');
		}
	   	
		print_r(User::getCurrentUser()->toArray());
       	echo 'error ' . $this->request->getValue('id');
	//	return new ActionResponse();
	}	
}

?>