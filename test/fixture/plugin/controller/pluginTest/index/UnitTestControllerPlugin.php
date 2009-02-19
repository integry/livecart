<?php

class UnitTestControllerPlugin extends ControllerPlugin
{
	public function process()
	{
		$this->response->set('success', true);
	}
}

?>