<?php

class UnitTestCurrencyArrayPlugin extends ModelPlugin
{
	public function process()
	{
		$this->object['testValue'] = true;
	}
}

?>