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

		$this->set('id', $this->request->get('id'));
		$this->set('ajax', $this->request->get('ajax'));
		$this->set('description', HTTPStatusException::getCodeMeaning($this->request->get('id')));

		$response->setStatusCode($this->request->get('id'));

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
				$this->set('id', $id);
				$response->setStatusCode($this->request->get('id'));
		}
	}

	public function backendBrowserAction()
	{

	}

	public function databaseAction()
	{
		$this->setLayout('empty');
		$this->set('error', $_REQUEST['exception']->getMessage());
	}
}

?>