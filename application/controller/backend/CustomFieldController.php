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
		$nodes = array();
		foreach (EavField::getEavClasses() as $class => $id)
		{
			$nodes[] = array('ID' => $id, 'name' => $this->translate($class));
		}

		// get offline payment methods
		$offlineMethods = array();
		foreach (OfflineTransactionHandler::getEnabledMethods() as $method)
		{
			$id = substr($method, -1);
			$offlineMethods[] = array('ID' => $method, 'name' => $this->config->get('OFFLINE_NAME_' . $id));
		}

		if ($this->config->get('CC_ENABLE'))
		{
			$offlineMethods[] = array('ID' => 'creditcard', 'name' => $this->config->get('CC_HANDLER'));
		}

		if ($offlineMethods)
		{
			$nodes[] = array('ID' => 'offline methods', 'name' => $this->translate('_offline_methods'), 'sub' => $offlineMethods);
		}

		$this->set('nodes', $nodes);
	}
}

?>