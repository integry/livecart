<?php


/**
 * Product Category controller
 *
 * @package application.controller.backend
 * @author Integry Systems
 *
 */
class SessionController extends StoreManagementController
{
	public function indexAction()
	{
		$this->loadLanguageFile('User');
		$response = new ActionResponse('email', $this->request->gget('email'));
		$response->setHeader('NeedLogin', 1);
		$response->set('return', $this->request->gget('return'));
		return $response;
	}

	/**
	 *  Process actual login
	 */
	public function doLoginAction()
	{
		$user = User::getInstanceByLogin($this->request->gget('email'), $this->request->gget('password'));
		if (!$user)
		{
			return new ActionRedirectResponse('backend.session', 'index', array('query' => array('failed' => 'true', 'email' => $this->request->gget('email'))));
		}

		// login
		SessionUser::setUser($user);

		if ($this->request->gget('return'))
		{
			return new RedirectResponse($this->request->gget('return'));
		}
		else
		{
			return new ActionRedirectResponse('backend.index', 'index');
		}
	}

	public function logoutAction()
	{
		SessionUser::destroy();
		return new ActionRedirectResponse('backend.session', 'index');
	}
}

?>