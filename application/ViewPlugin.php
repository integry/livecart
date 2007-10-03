<?php

/**
 * View plugin base class
 *
 * @package application.model
 * @author Integry Systems
 */
abstract class ViewPlugin
{
	protected $smarty;

	protected $application;
	
	public abstract function process($code);
	
	public function __construct(LiveCartSmarty $smarty, LiveCart $application)
	{
		$this->smarty = $smarty;
		$this->application = $application;
	}
}

?>