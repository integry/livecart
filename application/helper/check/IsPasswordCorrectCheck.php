<?php

ClassLoader::import("framework.request.validator.check.Check");

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
		return md5($value) == $this->user->password->get();
	}
}

?>