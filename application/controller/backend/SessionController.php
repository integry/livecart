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
		$response = new ActionResponse();
		$response->setHeader('NeedLogin', 1);
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
            return new ActionRedirectResponse('backend.session', 'index', array('query' => 'failed=true'));
        }

        // login
        SessionUser::setUser($user);
                		        
        return new ActionRedirectResponse('backend.index', 'index');
    }
    
	public function logout()
    {
		SessionUser::destroy();
		return new ActionRedirectResponse('backend.session', 'index');
	}
}

?>