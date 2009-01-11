<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");

/**
 * Generate reports and stats
 *
 * @package application.controller.backend
 * @author	Integry Systems
 * @role page
 */
class ReportController extends StoreManagementController
{
	/**
	 *	Main settings page
	 */
	public function index()
	{
		$response = new ActionResponse();

		return $response;
	}
}

?>