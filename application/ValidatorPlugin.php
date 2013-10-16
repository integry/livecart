<?php

/**
 * Validator plugin base class
 *
 * @package application
 * @author Integry Systems
 */
abstract class ValidatorPlugin extends \Phalcon\DI\Injectable
{
	protected $validator;

	public abstract function process();

	public function __construct(\Phalcon\Validation $validator, \Phalcon\DI\FactoryDefault $di)
	{
		$this->validator = $validator;
		$this->setDI($di);
	}
}

?>