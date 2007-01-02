<?php

ClassLoader::import("framework.ApplicationException");

/**
 * Exception that indicates an attempt to execute a restricted controller/action
 *
 * @package application.model
 * @author Saulius Rupainis <saulius@integry.net>
 */
class AccessDeniedException extends ApplicationException
{

	private $user = null;
	private $actionName = "";
	private $controllerName = "";

	public function __construct(User $user, $controllerName, $actionName)
	{
		parent::__construct("Access denied to action $controllerName.$actionName for user ".$user->getID());
		$this->user = $user;
		$this->controllerName = $controllerName;
		$this->actionName = $actionName;
	}

	public function getUser()
	{
		return $this->user;
	}

	public function getActionName()
	{
		return $this->actionName;
	}

	public function getContrllerName()
	{
		return $this->controllerName;
	}
}

?>