<?php

ClassLoader::import("application.controller.BaseController");
ClassLoader::import("library.json.json");

/**
 * Generic backend controller for administrative tools (actions, modules etc.)
 *
 * @author Integry Systems
 * @package application.backend.controller.abstract
 */
abstract class BackendController extends BaseController
{
	public function __construct(LiveCart $application)
	{
		if ($application->getConfig()->get('SSL_BACKEND'))
		{
			$application->getRouter()->setSslAction('');
		}

		parent::__construct($application);

		if (!isset($_SERVER['HTTP_USER_AGENT']))
		{
			$_SERVER['HTTP_USER_AGENT'] = 'Firefox';
		}

		// no IE yet
		if (preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT']))
		{
			ClassLoader::import('application.controller.backend.UnsupportedBrowserException');
			throw new UnsupportedBrowserException();
		}

		if (!$this->user->hasBackendAccess() && !($this instanceof SessionController))
		{
			SessionUser::destroy();

			$url = $this->router->createUrl(array('controller' => 'backend.session', 'action' => 'index'));
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
	}

	public function init()
	{
	  	$this->setLayout('empty');
		$this->addBlock('USER_MENU', 'boxUserMenu', 'block/backend/userMenu');
		$this->addBlock('TRANSLATIONS', 'translations', 'block/backend/translations');
		return parent::init();
	}

	public function boxUserMenuBlock()
	{
		return $this->translationsBlock();
	}

	public function translationsBlock()
	{
		return new BlockResponse('languageData', json_encode($this->locale->translationManager()->getLoadedDefinitions()));
	}

}

?>