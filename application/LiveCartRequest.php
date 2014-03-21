<?php

class LiveCartRequest extends \Phalcon\Http\Request
{
	protected $json = null;
	
	public function getJsonRawBody()
	{
		if (is_null($this->json))
		{
			$this->json = json_decode(file_get_contents("php://input"), true);
		}
		
		return $this->json;
	}
	
	public function getJson($key)
	{
		$json = $this->getJsonRawBody();
		
		if (is_array($json) && isset($json[$key]))
		{
			return $json[$key];
		}
	}
	
	public function getParam($key, $default = null)
	{
		$params = array_filter(array($this->getDI()->get('dispatcher')->getParam($key), $this->get($key), $this->getJson($key), $default));
		return array_shift($params);
	}
	
	public function set($key, $value)
	{
		$_REQUEST[$key] = $value;
	}
}
