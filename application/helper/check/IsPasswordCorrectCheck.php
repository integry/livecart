<?php

ClassLoader::import("framework.request.validator.check.Check");

/**
 * @package application.helper.check
 * @author Integry Systems
 */
class IsPasswordCorrectCheck extends Check
{
	private $user;

	public function __construct($violationMsg, User $user)
	{
		parent::__construct($violationMsg);
		$this->user = $user;
	}

	public function isValid($value)
	{
		return $this->user->isPasswordValid($value);
	}
}

?>