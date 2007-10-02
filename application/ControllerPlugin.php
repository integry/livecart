<?php

/**
 *  Allows to plug in controller response post-processors
 *
 *  @package application
 *  @author Integry Systems
 */
abstract class ControllerPlugin
{
	private $mustStop;	
	
	protected $response;

	protected $controller;
	
	protected $request;
	
	protected $controllerName;
	
	protected $actionName;
	
	public abstract function process();
	
	public function __construct(Response $response, Controller $controller)
	{
		$this->response = $response;	
		$this->controller = $controller;
		$this->request = $controller->getRequest();
		$this->controllerName = $this->request->getControllerName();
		$this->actionName = $this->request->getActionName();
	}
	
	public function getResponse()
	{
		return $this->response;
	}
	
	public function setResponse(Response $response)
	{
		$this->response = $response;
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