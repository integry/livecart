<?php

ClassLoader::import("framework.request.validator.check.Check");

class PasswordMatchCheck extends Check
{
	private $request;
	
	public function __construct($violationMsg, Request $request, $fieldName, $confFieldName)
	{
		parent::__construct($violationMsg);
		$this->setParam("fieldName", $fieldName);
		$this->setParam("confFieldName", $confFieldName);
		$this->request = $request;
	}
	
	public function isValid($value)
	{
		return $this->request->getValue($this->getParam("fieldName")) 
				== $this->request->getValue($this->getParam("confFieldName"));
	}
}

?>