<?php


/**
 * Product Category controller
 *
 * @package application/controller/backend
 * @author Integry Systems
 *
 */
class SessionController extends ControllerBackend
{
	public function indexAction()
	{
		$this->loadLanguageFile('User');
		$this->set('email', $this->request->get('email'));
		$this->set('return', $this->request->get('return'));
	}

	/**
	 *  Process actual login
	 */
	public function doLoginAction()
	{
		$user = \user\User::getInstanceByLogin($this->request->get('email'), $this->request->get('password'));
		if (!$user)
		{
			return new ActionRedirectResponse('backend.session', 'index', array('query' => array('failed' => 'true', 'email' => $this->request->get('email'))));
		}

		// login
		$this->sessionUser->setUser($user);

		if ($this->request->get('return'))
		{
			return $this->response->redirect('backend/index/index');
			return new RedirectResponse($this->request->get('return'));
		}
		else
		{
			return $this->response->redirect('backend/index/index');
		}
	}

	public function logoutAction()
	{
		$this->sessionUser->destroy();
		return $this->response->redirect('backend/session/index');
	}
}

?>
