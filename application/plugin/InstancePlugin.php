<?php

/**
 * Application run-time event plugin (startup, shutdown, etc.)
 *
 * @package application.plugin
 * @author Integry Systems
 */
abstract class InstancePlugin
{
	protected $application;
	protected $instance;
	protected $params;

	protected $request;
	protected $router;

	public function __construct($application, &$instance, $params = null)
	{
		$this->application = $application;
		$this->instance = $instance;
		$this->params = $params;

		$this->request = $this->application->getRequest();
		$this->router = $this->application->getRouter();
	}

	public function getApplication()
	{
		return $this->application;
	}

	abstract public function process();
}

?>