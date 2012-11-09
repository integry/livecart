<?php

class OutputCache
{
	protected $type;

	protected $params = array();

	protected $data;

	protected $parent;
	
	protected $isEnabled = true;

	public function __construct($type)
	{
		$this->type = $type;
		//$this->params = $params;
	}
	
	public function enable()
	{
		$this->isEnabled = true;
	}
	
	public function disable()
	{
		$this->isEnabled = false;
	}

	public function setParent(OutputCache $cache)
	{
		$this->parent = $cache;
	}

	public function getParent()
	{
		return $this->parent;
	}

	public function setData($data)
	{
		$this->data = $data;
	}

	public function save()
	{
		if (!$this->isEnabled)
		{
			return;
		}
		
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

	public function setCacheVar($param, $value)
	{
		$this->params[$param] = $value;
	}

	public function invalidateCacheOnUpdate($class)
	{
		
	}
}

?>
