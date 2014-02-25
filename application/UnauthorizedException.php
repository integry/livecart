<?php


/**
 * Exception that indicates an attempt to execute a restricted controller/action
 *
 * @package framework.controller.exception
 * @author Integry Systems
 */
class UnauthorizedException extends Exception
{
	private $isBackend;
	
	public function __construct($isBackend)
	{
		$this->isBackend = $isBackend;
	}
	
	public function isBackend()
	{
		return $this->isBackend;
	}
}

?>
