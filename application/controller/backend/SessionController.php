<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");

/**
 * Product Category controller
 *
 * @package application.controller.backend
 * @author Saulius Rupainis <saulius@integry.net>
 *
 */
class SessionController extends StoreManagementController
{
	public function index()
	{
		return new ActionResponse();
	}
}

?>