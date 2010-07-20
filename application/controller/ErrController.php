<?php

ClassLoader::import("application.controller.FrontendController");

/**
 * @author Integry Systems
 * @package application.controller
 */
class ErrController extends FrontendController
{
	public function init()
	{
		parent::init();
		$this->loadLanguageFile('Err');
	}

	public function index()
	{
		$response = new ActionResponse();
		$response->set('id', $this->request->get('id'));
		$response->set('ajax', $this->request->get('ajax'));
		$response->set('description', HTTPStatusException::getCodeMeaning($this->request->get('id')));

		$response->setStatusCode($this->request->get('id'));

		return $response;
	}

	public function redirect()
	{
		$id = $this->request->get('id');
		$params = array();

		if($this->isAjax())
		{
			$params['query'] = array('ajax' => 1);
		}

		$params['query'] = array('return' => $_SERVER['REQUEST_URI']);

		switch($id)
		{
			case 401:
				return new ActionRedirectResponse('user', 'login', $params);

			default:
				$response = new ActionResponse('id', $id);
				$response->setStatusCode($this->request->get('id'));
				return $response;
		}
	}

	public function backendBrowser()
	{
		return new ActionResponse();
	}

	public function database()
	{
		$this->setLayout('empty');
		return new ActionResponse('error', $_REQUEST['exception']->getMessage());
	}
}

?>