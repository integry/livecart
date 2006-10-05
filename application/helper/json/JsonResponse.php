<?php

ClassLoader::import('framework.response.*');
ClassLoader::import("library.json.*");

/**
 *
 * @package	helper.response
 * @author Denis Slaveckij <denis@integry.net>
 */
class JsonResponse extends Response {
	
	private $dataContainer = array();

	/**
	 *
	 * @param string $name
	 * @param mix $value
	 */
	public function setValue($name, $value) {
		
		$this->dataContainer[$name] = $value;
	}
	
	
	public function getData() {
		
		require_once("JSON.php");  	
		$json = new Services_JSON(); 		
		return $json->encode($this->dataContainer);		
	}

}

?>