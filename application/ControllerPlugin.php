<?php

/**
 *  Allows to plug in controller response post-processors
 *
 *  @package application
 *  @author Integry Systems
 */
abstract class ControllerPlugin extends \Phalcon\DI\Injectable
{
	private $mustStop;

	protected $controller;

	public abstract function process();

	public function __construct(ControllerBase $controller, \Phalcon\DI\FactoryDefault $di)
	{
		$this->setDI($di);
		$this->controller = $controller;
	}

	/**
	 *	Stop further execution of plugin chain for this action
	 */
	public function stop()
	{
		$this->mustStop = true;
	}

	public function isStopped()
	{
		return $this->mustStop;
	}
}

?>