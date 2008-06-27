<?php

ClassLoader::import('application.controller.backend.abstract.StoreManagementController');
ClassLoader::import('application.model.eav.EavField');

/**
 * Manage custom EAV fields
 *
 * @package application.controller.backend
 * @author Integry Systems
 *
 */
class CustomFieldController extends StoreManagementController
{
	public function init()
	{
		$this->loadLanguageFile('backend/Category');
		return parent::init();
	}

	public function index()
	{
		$nodes = array();
		foreach (EavField::getEavClasses() as $class => $id)
		{
			$nodes[] = array('ID' => $id, 'name' => $this->translate($class));
		}

		return new ActionResponse('nodes', $nodes);
	}
}

?>