<?php

class UnitTestControllerInitPlugin extends ControllerPlugin
{
	public function process()
	{
		$this->controller->testValue = true;
	}
}

?>