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
		  	  	  	
		$path = explode('.', $id);
		if (count($path) == 1)
		{
		 	$path[] = $path[0];
		}
		  	  	  	
	  	// get help template file
	  	$helpTemplate = 'backend/help/' . $lang . '/' . implode('/', $path) . '.tpl';
	  	
	  	// get breadcrumb path
	  	$currentPath = ClassLoader::getRealPath('application.view') . '/backend/help/' . $lang . '/';
	  	$breadCrumb = array();
		$helpId = '';
		foreach ($path as $dir)
	  	{
			$breadCrumb[$helpId] = file_get_contents($currentPath . 'path.txt');
			$helpId .= ('' != $helpId ? '.' : '') . $dir;
			$currentPath .= $dir . '/';
		}
	  	  	
	  	$response = new ActionResponse();
	  	$response->setValue('helpTemplate', $helpTemplate);
	  	$response->setValue('breadCrumb', $breadCrumb);
	  	return $response;
	}
  
}

?>