<?php

ClassLoader::import("framework.request.Session");
ClassLoader::import("framework.controller.Controller");
ClassLoader::import("application.model.user.User");

/**
 * Base controller for the whole application
 * 
 * Store controller which implements common operations needed for both frontend and 
 * backend
 *
 * @package application.controller
 * @author Saulius Rupainis <saulius@integry.net>
 */
abstract class BaseController extends Controller 
{

	/**
	 * System user
	 *
	 * @var User
	 */
	protected $user = null;
	
	/**
	 * Session instance
	 *
	 * @var Session
	 */
	protected $session = null;
	
	/**
	 * Router instance
	 *
	 * @var Router
	 */
	protected $router = null;
	
	/**
	 * Bese controller constructor: restores user object by using session data and 
	 * checks a permission to a requested action
	 *
	 * @param Request $request
	 * @throws AccessDeniedExeption
	 */
	public function __construct(Request $request) 
	{
		parent::__construct($request);
		
		$this->session = new Session();
		$user = $this->session->getValue("user");
		if (!empty($user)) 
		{
			$this->user = unserialize($user);
		} 
		else 
		{
			$this->user = User::getInstanceByID(User::ANONYMOUS_USER_ID);
		}
		$this->router = Router::getInstance();
	}
}

?>