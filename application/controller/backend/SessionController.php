<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import('application.model.user.SessionUser');

/**
 * Product Category controller
 *
 * @package application.controller.backend
 * @author Integry Systems
 *
 */
class SessionController extends StoreManagementController
{
	public function index()
	{
		$this->loadLanguageFile('User');
		$response = new ActionResponse('email', $this->request->get('email'));
		$response->setHeader('NeedLogin', 1);
		$response->set('return', $this->request->get('return'));
		return $response;
	}

	/**
	 *  Process actual login
	 */
	public function doLogin()
	{
		$user = User::getInstanceByLogin($this->request->get('email'), $this->request->get('password'));
		if (!$user)
		{
			return new ActionRedirectResponse('backend.session', 'index', array('query' => array('failed' => 'true', 'email' => $this->request->get('email'))));
		}

		// login
		SessionUser::setUser($user);

		if ($this->request->get('return'))
		{
			return new RedirectResponse($this->request->get('return'));
		}
		else
		{
			return new ActionRedirectResponse('backend.index', 'index');
		}
	}

	public function logout()
	{
		SessionUser::destroy();
		return new ActionRedirectResponse('backend.session', 'index');
	}
}

?>