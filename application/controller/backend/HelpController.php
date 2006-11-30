<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");

class HelpController extends StoreManagementController
{  
	function index()
	{	  	
	  	echo 'Ljaa ljaa';
	  	exit;	  
	}
	
	function view()
	{
		$this->setLayout('help/view');  
	  	$id = $this->request->getValue('id');
	  	$lang = $this->request->getValue('language');
	  	
		$lang = 'en';		  	  	  	
		  	  	  	
		$path = explode('.', $id);
		if ((count($path) == 1) && ('index' != $id))
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
			$breadCrumb[$helpId == '' ? 'index' : $helpId] = file_get_contents($currentPath . 'path.txt');
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