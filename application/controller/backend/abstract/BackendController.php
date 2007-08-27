<?php

ClassLoader::import("application.controller.BaseController");
ClassLoader::import("library.json.json");

/**
 * Generic backend controller for administrative tools (actions, modules etc.)
 *
 * @package application.backend.controller.abstract
 */
abstract class BackendController extends BaseController
{
    public function __construct(LiveCart $application)
    {
        if ($application->getConfig()->get('SSL_BACKEND'))
        {
            $application->getRouter()->setSslAction('');
        }

        parent::__construct($application);
    }
    
    public function init()
	{
	  	$this->setLayout('empty');
		$this->addBlock('USER_MENU', 'boxUserMenu', 'block/backend/userMenu');
	}
	
	public function boxUserMenuBlock()
	{
		$response = new BlockResponse();
		return $response;
	}
}

?>