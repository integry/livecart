<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");

/**
 * Handles dynamic interface customizations
 *
 * @package application.controller.backend
 * @author Rinalds Uzkalns <rinalds@integry.net>
 * @role admin.site.language
 */
class CustomizeController extends StoreManagementController
{
	public function index()
	{
		return new ActionResponse();		  	
	}	
}

?>