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
		curl_setopt($ch, CURLOPT_REFERER, $this->url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_TIMEOUT, 2);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.8.1.11) Gecko/20071204 Ubuntu/7.10 (gutsy) Firefox/2.0.0.11');

		$stream = fopen($this->tmpFile, 'w');
		curl_setopt($ch, CURLOPT_FILE, $stream);

		$res = curl_exec($ch);
		curl_close($ch);
		fclose($stream);

		return $res;
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
