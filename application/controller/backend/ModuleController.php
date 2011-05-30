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
	private $repos = array('http://localhost:8002');

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
		$repos = array();
		foreach ($this->repos as $repo)
		{
			if ($key = $this->getRepoHandshake($repo))
			{
				$repos[$repo] = $key;
			}
		}

		if (!$repos)
		{
			$this->setErrorMessage($this->translate('_err_no_repos'));
		}

		$conf = $this->application->getConfigContainer();

		$modules = array();
		foreach ($conf->getAvailableModules() as $module)
		{
			$modules[$module->getMountPath()] = $this->toArray($module);
		}

		$this->getNewestVersions($modules, $repos);

		usort($modules, array($this, 'sortModulesByName'));
		$sorted = array();

		foreach ($modules as $module)
		{
			if (!$module['isInstalled'])
			{
				$sorted['notInstalled'][] = $module;
			}
			else if (isset($module['newest']) && ($module['newest']['version'] != $module['Module']['version']))
			{
				$module['needUpdate'] = true;
				$sorted['needUpdate'][] = $module;
			}
			else if (!$module['isEnabled'])
			{
				$sorted['notEnabled'][] = $module;
			}
			else
			{
				$sorted['enabled'][] = $module;
			}
		}

		$response = new ActionResponse();
		$response->set('sortedModules', $sorted);
		return $response;
	}

	private function sortModulesByName($a, $b)
	{
		return strtolower($a['Module']['name']) > strtolower($b['Module']['name']) ? 1 : -1;
	}

	private function getRepoHandshake($repo)
	{
		$fetch = new NetworkFetch($repo . '/handshake?domain=localhost');
		$fetch->fetch();
		$res = json_decode(file_get_contents($fetch->getTmpFile()), true);

		if ('ok' == $res['status'])
		{
			$res['repo'] = $repo;
			return $res;
		}
	}

	private function getRepoResponse($query, $params = array(), $raw = false)
	{
		unset($params['package']);

		$module = $this->request->get('id');
		if (substr($module, 0, 7) == 'module.')
		{
			$module = substr($module, 7);
		}

		$url = $this->request->get('repo') . '/' . $query . '?domain=localhost&handshake=' . $this->request->get('handshake') . '&package=' . $module;
		foreach ($params as $key => $value)
		{
			$url .= '&' . $key . '=' . $value;
		}

		$fetch = new NetworkFetch($url);
		$fetch->fetch();

		return $raw ? file_get_contents($fetch->getTmpFile()) : json_decode(file_get_contents($fetch->getTmpFile()), true);
	}

	private function getNewestVersions(&$modules, $repos)
	{
		foreach ($modules as $identifier => &$details)
		{
			if (substr($identifier, 0, 7) == 'module.')
			{
				$identifier = substr($identifier, 7);
			}

			foreach ($repos as $repo)
			{
				if (isset($repo['packages'][$identifier]) && ($vers = $repo['packages'][$identifier]))
				{
					if (!isset($details['line']))
					{
						$details['line'] = 'current';
					}

					if (isset($vers[$details['line']]))
					{
						$details['newest'] = $vers[$details['line']];
						$details['newest']['time'] = $this->application->getLocale()->getFormattedTime($details['newest']['fileStat']['ctimeStamp']);

						$details['repo'] = $repo;
					}
				}
			}
		}
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

	public function updateMenu()
	{
		$lines = $this->getRepoResponse('package/channels');

		$response = new ActionResponse('lines', array_combine($lines, $lines));
		$response->set('versions', $this->getVersionList($this->getRepoResponse('package/versions', array('channel' => 'current'))));
		$response->set('form', new Form($this->getValidator('moduleUpdateMenu')));
		return $response;
	}

	public function listVersions()
	{
		return new JSONResponse($this->getVersionList($this->getRepoResponse('package/versions', array('channel' => $this->request->get('channel')))));
	}

	public function node()
	{
		return new ActionResponse('module', $this->toArray($this->getModule()));
	}

	public function update()
	{
		$response = new JSONResponse('');
		$updatePath = $this->getRepoResponse('package/updatePath', array('from' => $this->request->get('from'), 'to' => $this->request->get('to')));

		$flush = array('path' => $updatePath);
		$flush['status'] = $this->translate($updatePath ? '_status_fetch' : '_status_nothing_to_fetch');
		$response->flush(base64_encode(json_encode($flush)) . '|');

		if (!$updatePath)
		{
			return $response;
		}

		require_once(ClassLoader::getRealPath('library.pclzip') . '/pclzip.lib.php');

		// process update
		$tmpFile = ClassLoader::getRealPath('cache.') . 'update' . rand(1, 5000000) . '.zip';
		$tmpDir = substr($tmpFile, 0, -4);

		foreach ($updatePath as $key => $package)
		{
			file_put_contents($tmpFile, $this->getRepoResponse('package/download', $package, true));
			$archive = new PclZip($tmpFile);
			mkdir($tmpDir);
			$archive->extract($tmpDir);
			unlink($tmpFile);


		}
	}

	private function getVersionList($repoResponse)
	{
		$versions = array();
		foreach ($repoResponse as $version)
		{
			$version['time'] = $this->application->getLocale()->getFormattedTime($version['fileStat']['ctimeStamp']);
			$versions[$version['version']] = $version['version'] . ' (' . $version['time']['date_medium'] . ')';
		}

		return array_reverse($versions);
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

		if (empty($info['Module']['line']))
		{
			$info['Module']['line'] = 'current';
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
