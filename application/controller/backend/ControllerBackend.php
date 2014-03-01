<?php

require_once(dirname(dirname(__FILE__)) . '/ControllerBase.php');

/**
 * Generic backend controller for administrative tools (actions, modules etc.)
 *
 * @author Integry Systems
 * @package application/backend/controller/abstract
 */
abstract class ControllerBackend extends ControllerBase
{
	public function initialize()
	{
		parent::initialize();
		
		$this->loadLanguageFile('backend/abstract/Backend');
		
		$user = $this->sessionUser->getUser();
		if ((!$user || !$user->getID() || !$user->userGroupID)  && !($this instanceof SessionController))
		{
			header('Location: ' . $this->url->get('user/logout'));
			die('');
		}
		
/*
		if ($application->getConfig()->get('SSL_BACKEND'))
		{
			$application->getRouter()->setSslAction('');
		}

		if (!isset($_SERVER['HTTP_USER_AGENT']))
		{
			$_SERVER['HTTP_USER_AGENT'] = 'Firefox';
		}

		// no IE yet
		if (preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT']))
		{
						throw new UnsupportedBrowserException();
		}

		if (!$this->user->hasBackendAccess() && !($this instanceof SessionController))
		{
			$this->sessionUser->destroy();

			$url = $this->url->get('backend.session/index', 'query' => array('return' => $_SERVER['REQUEST_URI'])));
			if (!$this->isAjax())
			{
				header('Location: ' . $url);
			}
			else
			{
				header('Content-type: text/javascript');
				echo json_encode(array('__redirect' => $url));
			}

			exit;
		}
	*/
	}
	
/*
	public function initialize()
	{
	  	$this->setLayout('empty');
		$this->addBlock('USER_MENU', 'boxUserMenu', 'block/backend/userMenu');
		$this->addBlock('TRANSLATIONS', 'translations', 'block/backend/translations');

		$this->addBlock('FOOTER_TOOLBAR', 'toolbar', 'block/backend/toolbar');

		$this->getPendingModuleUpdateStats($this->application);

		return parent::initialize();
	}

	protected function getPendingModuleUpdateStats(LiveCart $application)
	{
		$config = $application->getConfig();

		// modules needing update
		if (!$config->has('MODULE_STATS_UPDATED') || (time() - $config->get('MODULE_STATS_UPDATED') > 3600))
		{
			$config->set('MODULE_STATS_UPDATED', time());
			$config->save();
			$controller = new ModuleController($this->application);
			$controller->initRepos();
			$updateResponse = $controller->index();
			$modules = $updateResponse->get('sortedModules');
			$config->set('MODULE_STATS_NEED_UPDATING', isset($modules['needUpdate']) ? count($modules['needUpdate']) : 0);
			$config->save();

			foreach ($this->getConfigFiles() as $file)
			{
				$this->loadLanguageFile($file);
			}
		}
	}

	public function boxUserMenuBlockAction()
	{
		return $this->translationsBlock();
	}

	public function translationsBlockAction()
	{
		return new BlockResponse('languageData', json_encode($this->locale->translationManager()->getLoadedDefinitions()));
	}

	public function toolbarBlockAction()
	{
		$response = new BlockResponse();
		$this->set('dropButtons',
			BackendToolbarItem::sanitizeItemArray(
				BackendToolbarItem::getUserToolbarItems(BackendToolbarItem::TYPE_MENU)
			)
		);
	}
*/
	
}

?>
