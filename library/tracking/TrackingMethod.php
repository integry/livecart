<?php

abstract class TrackingMethod
{
	private $data = array();

	protected $controller;

	public final function __construct($data, Controller $controller)
	{
		$this->data = $data;
		$this->controller = $controller;
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