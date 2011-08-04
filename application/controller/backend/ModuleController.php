<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.system.ModuleRepo");

/**
 * Manage application modules
 *
 * @package application.controller.backend
 * @author Integry Systems
 * @role delivery
 */
class ModuleController extends StoreManagementController
{
	private $repos = array();

	public function init()
	{
		$this->initRepos();

		parent::init();
	}

	protected function initRepos()
	{
		$conf = $this->application->getConfigContainer();
		$conf->clearCache();
		foreach ($conf->getAvailableModules() as $module)
		{
			$this->locale->translationManager()->setDefinitionFileDir(array_shift($module->getLanguageDirectories()));
		}

		$this->locale->translationManager()->reloadFile('Base');

		$this->repos = ModuleRepo::getConfiguredRepos($this->application, $this->getDomain());
	}

	private function getDomain()
	{
		$baseUrl = $this->application->getRouter()->getBaseUrl();
		$parts = parse_url($baseUrl);
		$domain = $parts['host'];

		return $domain;
	}

	/**
	 * Main settings page
	 */
	public function index()
	{
		$repos = array();
		foreach ($this->repos as $repo)
		{
			if ($handshake = $repo->getHandshake())
			{
				$repos[$handshake['repo']] = $handshake;
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

		$modules['.'] = $this->toArray($conf->getModule('.'));

		$this->getNewestVersions($modules, $repos);

		usort($modules, array($this, 'sortModulesByName'));

		$livecart = array_pop($modules);
		$livecart['isInstalled'] = true;
		$livecart['isEnabled'] = true;
		array_unshift($modules, $livecart);

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
		$response->set('repos', $repos);
		return $response;
	}

	private function sortModulesByName($a, $b)
	{
		return (strtolower($a['Module']['name']) > strtolower($b['Module']['name'])) ||
				('LiveCart' == $a['Module']['name'])
				 ? 1 : -1;
	}

	private function getRepoResponse($query, $params = array(), $raw = false)
	{
		$repo = $this->repos[$this->request->get('repo')];
		$repo->setHandshake($this->request->get('handshake'));
		$params['package'] = $this->request->get('id');

		return $repo->getResponse($query, $params, $raw);
	}

	private function getNewestVersions(&$modules, $repos)
	{
		foreach ($modules as $identifier => &$details)
		{
			if (substr($identifier, 0, 7) == 'module.')
			{
				$identifier = substr($identifier, 7);
			}
			else if ('.' == $identifier)
			{
				$identifier = 'livecart';
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
		$moduleInfo = $this->getModule()->getInfo();
		$moduleLine = isset($moduleInfo['Module']['line']) ? $moduleInfo['Module']['line'] : 'current';
		$lines = $this->getRepoResponse('package/channels');

		$form = new Form($this->getValidator('moduleUpdateMenu'));
		$form->set('channel', $moduleLine);

		$response = new ActionResponse('lines', array_combine($lines, $lines));
		$response->set('versions', $this->getVersionList($this->getRepoResponse('package/versions', array('channel' => $moduleLine))));
		$response->set('form', $form);
		return $response;
	}

	public function listVersions()
	{
		return new JSONResponse($this->getVersionList($this->getRepoResponse('package/versions', array('channel' => $this->request->get('channel')))));
	}

	public function node()
	{
		$module = $this->toArray($this->getModule());
		$module['repo'] = array('repo' => $this->request->get('repo'), 'handshake' => $this->request->get('handshake'));
		return new ActionResponse('module', $module);
	}

	public function update()
	{
		$this->config->set('MODULE_STATS_UPDATED', null);
		$this->config->save();

		$response = new JSONResponse('');
		$updatePath = $this->getRepoResponse('package/updatePath', array('from' => $this->request->get('from'), 'to' => $this->request->get('to')));

		$flush = array('path' => $updatePath);
		$flush['status'] = $this->translate($updatePath ? '_status_fetch' : '_status_nothing_to_fetch');
		$response->flushChunk($flush);

		if (!$updatePath)
		{
			return $response;
		}

		require_once(ClassLoader::getRealPath('library.pclzip') . '/pclzip.lib.php');

		// process update
		$module = $this->application->getConfigContainer()->getModule($this->request->get('id'));

		foreach ($updatePath as $key => $package)
		{
			$tmpFile = ClassLoader::getRealPath('cache.') . 'update' . rand(1, 5000000) . '.zip';
			$tmpDir = substr($tmpFile, 0, -4);

			$response->flushChunk(array('package' => $package, 'status' => $this->translate('_status_fetch')));

			file_put_contents($tmpFile, $this->getRepoResponse('package/download', $package, true));
			$archive = new PclZip($tmpFile);
			mkdir($tmpDir);
			$archive->extract($tmpDir);
			unlink($tmpFile);

			$res = $module->applyUpdate($tmpDir);
			if ($res === true)
			{
				$response->flushChunk(array('status' => 'ok'));
			}
			else
			{
				$errs = array('_err_update_copy_msg', '_err_update_db', '_err_update_custom');
				$response->flushChunk(array('status' => 'err', 'msg' => $this->maketext($errs[$res[0]], array($res[1]))));

				$res = false;
			}

			$this->application->rmdir_recurse($tmpDir);

			if (!$res)
			{
				return;
			}
		}

		$response->flushChunk(array('final' => $this->makeText('_update_complete', array($this->translate($module->getName())))));
	}

	public function repoStatus()
	{
		$repo = $this->request->get('repo');
		if (!preg_match('/^http[s]{0,1}\:\/\//', $repo))
		{
			return new RawResponse('Invalid URL');
		}

		$fetch = new NetworkFetch($repo . '/ping');
		$fetch->fetch();
		return new RawResponse(file_get_contents($fetch->getTmpFile()) == 'OK' ? 'OK' : 'fail');
	}

	public function repoDescription()
	{
		$repo = $this->request->get('repo');
		if (!preg_match('/^http[s]{0,1}\:\/\//', $repo))
		{
			return new RawResponse('Invalid URL');
		}

		$fetch = new NetworkFetch($repo);
		$fetch->fetch();
		return new RawResponse(file_get_contents($fetch->getTmpFile()));
	}

	public function packageList()
	{
		$resp = array();
		$conf = $this->application->getConfigContainer();
		foreach (json_decode($this->request->get('repos'), true) as $repo)
		{
			$this->request->set('repo', $repo['repo']);
			$this->request->set('handshake', $repo['handshake']);

			$p = parse_url($repo['repo']);
			foreach ($this->getRepoResponse('package/list') as $package)
			{
				if ($conf->getModule('module.' . $package['pkg']))
				{
					continue;
				}

				$id = base64_encode(serialize(array($repo['repo'], $package['pkg'])));
				$resp[$p['host']][$id] = $package;
			}
		}

		$response = new ActionResponse();
		$response->set('repos', base64_encode($this->request->get('repos')));
		$response->set('packages', $resp);
		return $response;
	}

	public function fetch()
	{
		require_once(ClassLoader::getRealPath('library.pclzip') . '/pclzip.lib.php');

		$id = unserialize(base64_decode($this->request->get('module')));
		$repos = json_decode(base64_decode($this->request->get('repos')), true);

		$repo = $repos[$id[0]];
		$this->request->set('repo', $repo['repo']);
		$this->request->set('handshake', $repo['handshake']);
		$this->request->set('id', $id[1]);

		$tmpFile = ClassLoader::getRealPath('cache.') . 'install' . rand(1, 5000000) . '.zip';
		file_put_contents($tmpFile, $this->getRepoResponse('package/downloadInstall', array(), true));

		if (!filesize($tmpFile))
		{
			return new JSONResponse(array('error' => $this->translate('_err_download_package')));
		}

		$tmpDir = substr($tmpFile, 0, -4);
		mkdir($tmpDir);
		$archive = new PclZip($tmpFile);
		$archive->extract($tmpDir);
		unlink($tmpFile);

		$update = new UpdateHelper($this->application);
		$moduleDir = ClassLoader::getRealPath('module.' . $id[1]);
		$copy = $update->copyDirectory($tmpDir, $moduleDir);

		if ($copy !== true)
		{
			return new JSONResponse(array('error' => $this->maketext('_err_update_copy_msg', array($copy))));
		}

		$this->application->getConfigContainer()->addModule('module.' . $id[1]);
		$module = $this->application->getConfigContainer()->getModule('module.' . $id[1]);
		$module->install($this->application);
		$module->setStatus(true);

		$this->request->set('id', 'module.' . $id[1]);
		return $this->node();
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
