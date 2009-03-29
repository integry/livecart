<?php

/**
 * Application run-time event plugin (startup, shutdown, etc.)
 *
 * @package application.plugin
 * @author Integry Systems
 */
abstract class ProcessPlugin
{
	protected $application;

	public function __construct($application)
	{
		$this->application = $application;
	}

	abstract public function process();
}

?>