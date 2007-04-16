<?php

ClassLoader::import("application.controller.FrontendController");

class ErrorController extends FrontendController
{
	function error404()
	{
	echo 'error 404';
	//	return new ActionResponse();
	}	
}

?>