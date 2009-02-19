<?php

class UnitTestBaseControllerInitPlugin extends ControllerPlugin
{
	public function process()
	{
		$this->controller->baseInitValue = true;
	}
}

?>