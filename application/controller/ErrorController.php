<?php

ClassLoader::import("application.controller.FrontendController");

class ErrorController extends FrontendController
{
	function index()
	{
		$id = $this->request->getValue('id');
		if (401 == $id)
		{
			return new ActionRedirectResponse('user', 'login');
		}
	   	
		print_r(User::getCurrentUser()->toArray());
       	echo 'error ' . $this->request->getValue('id');
	//	return new ActionResponse();
	}	
}

?>