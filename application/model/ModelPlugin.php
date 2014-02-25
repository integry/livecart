<?php

/**
 * Model plugin base class
 *
 * @package application/model
 * @author Integry Systems
 */
abstract class ModelPlugin extends \Phalcon\DI\Injectable
{
	protected $object;

	protected $application;
	
	public abstract function process();
	
	public function __construct(&$object, $di)
	{
		$this->object =& $object;
		$this->setDI($di);
		$this->process();
	}
}

?>
