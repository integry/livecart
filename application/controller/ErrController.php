<?php


/**
 * @author Integry Systems
 * @package application/controller
 */
class ErrController extends FrontendController
{
	public function indexAction()
	{
		$this->response->setHeader(404, 'Not Found');
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

	public function databaseAction()
	{
		$this->setLayout('empty');
		$this->set('error', $_REQUEST['exception']->getMessage());
	}
}

?>
