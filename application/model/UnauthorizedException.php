<?php

ClassLoader::import("framework.ApplicationException");

/**
 * Exception that indicates an attempt to execute a restricted controller/action
 *
 * @package application.model
 * @author Saulius Rupainis <saulius@integry.net>
 */
class UnauthorizedException extends ApplicationException
{
	private $user = null;
	private $actionName = "";
	private $controllerName = "";
	private $roleName = "";

	public function __construct(User $user, $controllerName, $actionName, $roleName)
	{
		parent::__construct("User isn't authorized to use \"$controllerName.$actionName\" (role: \"$roleName\") for user ".$user->getID());
		$this->user = $user;
		$this->controllerName = $controllerName;
		$this->actionName = $actionName;
		$this->roleName = $roleName;
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
	
	public function getRoleName()
	{
	    return $this->roleName;
	}
}

?>