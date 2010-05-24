<?php

/**
 * Base for API request parsing (reading/routing)
 *
 * @package application.model.datasync
 * @author Integry Systems <http://integry.com>
 */

class ApiReader implements Iterator
{
	protected $iteratorKey = 0;
	protected $content;
	protected $xmlKeyToApiActionMapping = array();
	
	private $apiActionName;
	private $fieldNames = array();
	
	public function setApiFieldNames($fieldNames)
	{
		$this->fieldNames = $fieldNames;
	}

	public function getApiFieldNames()
	{
		return $this->fieldNames;
	}
	
	public function getApiActionName()
	{
		return $this->apiActionName;
	}
	
	public function setApiActionName($apiActionName)
	{
		$this->apiActionName=$apiActionName;
	}

	public function addItem($item)
	{
		$this->content[] = $item;
	}

	// -- Iterator methods
	public function rewind()
	{
		$this->iteratorKey = 0;
	}

	public function valid()
	{
		return $this->iteratorKey < count($this->content);
	}

	public function next()
	{
		$this->iteratorKey++;
	}

	public function key()
	{
		return $this->iteratorKey;
	}

	public function current()
	{
		return $this->content[$this->iteratorKey];
	}
	// --

	protected static function getSanitizedSimpleXml($xmlString)
	{
		try {
			$xmlRequest = @simplexml_load_string($xmlString);
			if(!is_object($xmlRequest) || $xmlRequest->getName() != 'request') {
				$xmlRequest = @simplexml_load_string('<request>'.$xmlString.'</request>');
			}
		} catch(Exception $e) {
			$xmlRequest = null;
		}
		if(!is_object($xmlRequest) || $xmlRequest->getName() != 'request') { // still nothing?
			throw new Exception('Bad request');
		}
		return $xmlRequest;
	}

	/*abstract?*/ protected function findApiActionName($dataHandler)
	{
		
	}

	public function loadDataInRequest($request, $xpathPrefix, $fieldNames)
	{
		foreach($fieldNames as $fieldName)
		{
			$d = $this->xml->xpath($xpathPrefix.$fieldName);
			if(count($d) == 1)
			{
				$v = (string)array_shift($d);
				$request->set($fieldName, $this->sanitizeField($fieldName, $v));
			}
		}
		return $request;
	}

	protected function sanitizeField($fieldName, &$value)
	{
		// switch($fieldName)
		// {
		//	case 'ID': // lets make ID always numeric (and -1 if invalid value).
		//		$value = intval($value);
		//		if(($value > 0) == false)
		//		{
		//			$value = -1;
		//		}
		//		break;
		// }
		return $value;
	}
}
