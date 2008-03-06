<?php

class OutputCache
{
	protected $type;

	protected $params;

	protected $data;

	public function __construct($type, $params)
	{
		$this->type = $type;
		$this->params = $params;
	}

	public function setData($data)
	{
		$this->data = $data;
	}

	public function save()
	{
		$path = $this->getFilePath();
		if (!file_exists(dirname($path)))
		{
			mkdir(dirname($path), 0777, true);
		}

		file_put_contents($path, $this->data);
	}

	public function getData()
	{
		if (!$this->data && $this->isCached())
		{
			$this->data = file_get_contents($this->getFilePath());
		}

		return $this->data;
	}

	public function getAge()
	{
		if ($this->isCached())
		{
			return time() - filemtime($this->getFilePath());
		}
	}

	public function isCached()
	{
		return file_exists($this->getFilePath());
	}

	public function getFilePath()
	{
		if (is_array($this->params))
		{
			$hash = md5(implode('-', array_values($this->params)));
		}
		else
		{
			$hash = md5($this->params);
		}

		return ClassLoader::getRealPath('cache.output.' . $this->type . '.') . $hash;
	}

}

?>