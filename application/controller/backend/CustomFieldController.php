<?php


/**
 * Manage custom EAV fields
 *
 * @package application/controller/backend
 * @author Integry Systems
 *
 */
class CustomFieldController extends StoreManagementController
{
	public function initialize()
	{
		$this->loadLanguageFile('backend/Category');
		$this->loadLanguageFile('backend/CustomField');
		return parent::initialize();
	}

	public function indexAction()
	{

	}
}

?>
