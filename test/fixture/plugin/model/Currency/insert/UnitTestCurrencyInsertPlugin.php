<?php

class UnitTestCurrencyInsertPlugin extends ModelPlugin
{
	public function process()
	{
		$this->object->rate->set(0.5);
		$this->object->isEnabled->set(false);
	}
}

?>