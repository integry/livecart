<?php


/**
 * @author Integry Systems
 * @package application/controller
 */
class ErrController extends FrontendController
{
	public function initialize()
	{
		parent::initialize();
		$this->loadLanguageFile('Err');
	}

	public function indexAction()
	{
		$response = new ActionResponse();
		$response->set('id', $this->request->get('id'));
		$response->set('ajax', $this->request->get('ajax'));
		$response->set('description', HTTPStatusException::getCodeMeaning($this->request->get('id')));

		$response->setStatusCode($this->request->get('id'));

		return $response;
	}

	public function redirectAction()
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

	public function backendBrowserAction()
	{
		return new ActionResponse();
	}

	public function databaseAction()
	{
		$this->setLayout('empty');
		return new ActionResponse('error', $_REQUEST['exception']->getMessage());
	}
}

?>