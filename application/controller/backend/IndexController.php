<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");

/**
 * Main backend controller which stands as an entry point to administration functionality
 *
 * @package application.controller.backend
 * @author Integry Systems <http://integry.com>
 *
 * @role login
 */
class IndexController extends StoreManagementController
{
	public function index()
	{
		return new ActionResponse();
	}
}

?>
