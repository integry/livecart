<?php

if (!class_exists('InstallCompat'))
{
	require_once dirname(dirname(__file__)) . '/InstallCompat.php';
}

class GodaddySharedHostingCompat extends InstallCompat
{
	public function isApplicable()
	{
		$system = $this->getParsedConfig('System');
		return strpos($system, 'secureserver.net') && strpos($system, 'shr');
	}

	public function apply()
	{
		$config = $this->application->getConfig();
		$config->set('PROXY_HOST', 'proxy.shr.secureserver.net');
		$config->set('PROXY_PORT', 3128);
	}
}

?>