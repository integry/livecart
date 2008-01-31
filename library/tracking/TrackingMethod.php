<?php

abstract class TrackingMethod
{
	private $data = array();

	public final function __construct($data)
	{
		$this->data = $data;
	}

	public function getValue($key)
	{
		if (isset($this->data[$key]))
		{
			return $this->data[$key];
		}
	}

	abstract public function getHtml();
}

?>