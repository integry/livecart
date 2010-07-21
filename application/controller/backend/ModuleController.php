<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");

/**
 * Manage application modules
 *
 * @package application.controller.backend
 * @author Integry Systems
 * @role delivery
 */
class ModuleController extends StoreManagementController
{
	public function init()
	{
		$conf = $this->application->getConfigContainer();
		$conf->clearCache();
		foreach ($conf->getAvailableModules() as $module)
		{
			$this->locale->translationManager()->setDefinitionFileDir(array_shift($module->getLanguageDirectories()));
		}

		$this->locale->translationManager()->reloadFile('Base');

		parent::init();
	}

	/**
	 * Main settings page
	 */
	public function index()
	{
		$conf = $this->application->getConfigContainer();

		$modules = array();
		foreach ($conf->getAvailableModules() as $module)
		{
			$modules[$module->getMountPath()] = $this->toArray($module);
		}

		$response = new ActionResponse();
		$response->set('modules', $modules);
		return $response;
	}

	public function setStatus()
	{
		$module = $this->getModule();
		$module->setStatus($this->request->get('state') == 'true', $this->application);
		return $this->statusResponse($this->request->get('state') == 'true' ? '_enable_confirm' : '_disable_confirm', $module);
	}

	public function install()
	{
		$module = $this->getModule();
		$module->install($this->application);
		return $this->statusResponse('_install_confirm', $module);
	}

	public function deinstall()
	{
		$module = $this->getModule();
		$module->deinstall($this->application);
		return $this->statusResponse('_deinstall_confirm', $module);
	}

	public function node()
	{
		return new ActionResponse('module', $this->toArray($this->getModule()));
	}

	private function toArray(ConfigurationContainer $module)
	{
		foreach ($module->getLanguageDirectories() as $dir)
		{
			$this->locale->translationManager()->setDefinitionFileDir($dir);
		}
		$this->loadLanguageFile('Base');

		$module->loadInfo();

		$info = $module->getInfo();

		foreach (array('name', 'description', 'description_full') as $field)
		{
			$info['Module'][$field] = !empty($info['Module']) && is_array($info['Module']) && array_key_exists($field, $info['Module']) ? $this->translate($info['Module'][$field]) : '';
		}

		$info['isEnabled'] = $module->isEnabled($this->application);
		$info['isInstalled'] = $module->isInstalled($this->application);

		return $info;
	}

	private function statusResponse($statusMsg, ConfigurationContainer $module)
	{
		$response = new CompositeJSONResponse();
		$response->setResponse('status', new JSONResponse(array('status' => $this->makeText($statusMsg, array($this->translate($module->getName()))))));
		$response->addAction('node', 'backend.module', 'node');
		return $response;
	}

	private function getModule()
	{
		return $this->application->getConfigContainer()->getModule($this->request->get('id'));
	}
}

?>
