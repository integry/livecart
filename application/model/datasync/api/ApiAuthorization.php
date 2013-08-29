<?php

/**
 * Abstract class for API authorization methods
 *
 * @package application/model/datasync
 * @author Integry Systems <http://integry.com>
 *
 */

abstract class ApiAuthorization
{
	protected $application;

	public function __construct(LiveCart $application, $params)
	{
		$this->application = $application;
		$this->params = $params;
	}

	abstract public function isValid();

	public function isAuthorized()
	{
		// leave room for overriding authorization result for special cases
		return $this->isValid();
	}
}

?>
