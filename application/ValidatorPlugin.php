<?php

/**
 * Validator plugin base class
 *
 * @package application
 * @author Integry Systems
 */
abstract class ValidatorPlugin
{
	protected $validator;

	protected $application;

	public abstract function process();

	public function __construct(\Phalcon\Validation $validator, LiveCart $application)
	{
		$this->validator = $validator;
		$this->application = $application;
	}
}

?>