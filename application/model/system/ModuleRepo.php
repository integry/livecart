<?php

class ModuleRepo
{
	private $application;
	private $url;
	private $domain;

	private $handshake;

	public function __construct(LiveCart $application, $url, $domain)
	{
		$this->application = $application;
		$this->url = $url;
		$this->domain = $domain;
	}

	public function getUrl()
	{
		return $this->url;
	}

	public function getHandshake()
	{
		// temporarily disable repos
		return;
		
		$fetch = new NetworkFetch($this->url . '/handshake?domain=' . $this->domain);
		$fetch->fetch();
		$res = json_decode(file_get_contents($fetch->getTmpFile()), true);

		if ('ok' == $res['status'])
		{
			$res['repo'] = $this->url;
			return $res;
		}
	}

	public function setHandshake($handshake)
	{
		$this->handshake = $handshake;
	}

	public function getResponse($query, $params = array(), $raw = false)
	{
		if (!empty($params['package']))
		{
			$module = $params['package'];

			if (substr($module, 0, 7) == 'module.')
			{
				$module = substr($module, 7);
			}
			else if ('.' == $module)
			{
				$module = 'livecart';
			}

			$params['package'] = $module;
		}

		$params['handshake'] = $this->handshake;

		$url = $this->url . '/' . $query . '?domain=' . $this->domain;
		foreach ($params as $key => $value)
		{
			$url .= '&' . $key . '=' . $value;
		}

		$fetch = new NetworkFetch($url);
		$fetch->fetch();

		return $raw ? file_get_contents($fetch->getTmpFile()) : json_decode(file_get_contents($fetch->getTmpFile()), true);
	}

	public static function getConfiguredRepos(LiveCart $application, $domain)
	{
		$k = 0;
		$repos = array();
		$config = $application->getConfig();
		while ($config->isValueSet('UPDATE_REPO_' . ++$k))
		{
			$repo = $config->get('UPDATE_REPO_' . $k);
			if ($repo)
			{
				$repos[$repo] = new ModuleRepo($application, $repo, $domain);
			}
		}

		return $repos;
	}
}

?>
