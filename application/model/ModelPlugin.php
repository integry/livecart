<?php

/**
 * Model plugin base class
 *
 * @package application/model
 * @author Integry Systems
 */
abstract class ModelPlugin
{
	protected $object;

	protected $application;
	
	public abstract function process();
	
	public function __construct(&$object, LiveCart $application)
	{
		$this->object =& $object;	
		$this->application = $application;
		$this->process();
	}
}

?>