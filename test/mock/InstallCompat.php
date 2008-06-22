<?php

abstract class InstallCompat
{
	protected $application;

	private $config = array();

	public function __construct(LiveCart $application)
	{
		$this->application = $application;
	}

	public abstract function IsApplicable();
	public abstract function apply();

	public function setConfig($key, $value)
	{
		$this->config[$key] = $value;
	}

	protected function getParsedConfig($what = '')
	{
		return $this->config[$what];
	}
}

?>