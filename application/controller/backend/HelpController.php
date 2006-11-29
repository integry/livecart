<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");

class HelpController extends StoreManagementController
{  
	function __construct($request)
	{
		parent::__construct($request);
		$this->setLayout('help');  
	}
	
	function index()
	{	  	
	  	echo 'Ljaa ljaa';
	  	exit;	  
	}
	
	function view()
	{
	  	$id = $this->request->getValue('id');
	  	$lang = $this->request->getValue('language');
	  	
		$lang = 'en';		  	  	  	
		  	  	  	
	  	// get help template file
	  	$helpTemplate = 'backend/help/' . $lang . '/' . str_replace('.', '/', $id) . '.tpl';
	  	
	  	$response = new ActionResponse();
	  	$response->setValue('helpTemplate', $helpTemplate);
	  	return $response;
	}
  
}

?>