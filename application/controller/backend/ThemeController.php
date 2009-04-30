<?php

ClassLoader::import('application.controller.backend.abstract.StoreManagementController');

/**
 * Manage design themes
 *
 * @package application.controller.backend
 * @author Integry Systems
 */
class ThemeController extends StoreManagementController
{
	public function index()
	{
		var_dump($this->application->getRenderer()->getThemeList());
		//return new ActionResponse('themes', $this->application->getRenderer()->getThemeList());
	}
}

?>