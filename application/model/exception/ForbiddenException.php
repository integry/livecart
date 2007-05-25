<?php

ClassLoader::import("framework.ApplicationException");

/**
 * Exception that indicates an attempt to execute a restricted controller/action
 *
 * @package application.model
 * @author Saulius Rupainis <saulius@integry.net>
 */
class ForbiddenException extends HTTPStatusException 
{	
	const STATUS_CODE = 403;

	public function __construct(Controller $controller, $message = false)
	{
	    parent::__construct($controller, self::STATUS_CODE, $message);
	}
}

?>