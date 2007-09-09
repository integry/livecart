<?php

ClassLoader::import('application.model.system.Installer');

class InstallController extends FrontendController
{
    public function init()
	{
	  	$this->setLayout('install');
	
		
//	  	$this->addBlock('CATEGORY_BOX', 'boxCategory', 'block/box/category');
	}

	public function index()
	{
		$response = new ActionResponse();
		$response->set('requirements', Installer::checkRequirements($this->application));
		return $response;
	}	
}

?>