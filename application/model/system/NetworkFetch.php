<?php

class NetworkFetch
{
	private $url;

	private $tmpFile;

	public function __construct($url)
	{
		$this->url = $url;
		$this->tmpFile = ClassLoader::getRealPath('cache.') . uniqid();
	}

	public function fetch()
	{
		if (extension_loaded('curl'))
		{
			return $this->fetchWithCurl();
		}
		else if (ini_get('allow_url_fopen'))
		{
			return $this->fetchWithCopy();
		}
	}

	public function fetchWithCurl()
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url);

		$stream = fopen($this->tmpFile, 'w');
		curl_setopt($ch, CURLOPT_FILE, $stream);

		return curl_exec($ch);
	}

	public function fetchWithCopy()
	{
		return @copy($this->url, $this->tmpFile);
	}

	public function getTmpFile()
	{
		return $this->tmpFile;
	}

	public function __destruct()
	{
		if (file_exists($this->tmpFile))
		{
			unlink($this->tmpFile);
		}
	}
}

?>