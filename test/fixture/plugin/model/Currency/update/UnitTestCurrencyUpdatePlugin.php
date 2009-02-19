<?php

class UnitTestCurrencyUpdatePlugin extends ModelPlugin
{
	public function process()
	{
		$this->object->isEnabled->set(true);
	}
}

?>