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

	//
	// this method really does not bellong here, but at this point, there is no better place.
	protected function findApiActionNameFromXml($xml, $xpath)
	{
		$apiActionName = null; // not known
		foreach($xml->xpath($xpath) as $k=>$v) // iterate over category,user,product etc elements
		{
			foreach($v as $k2 => $v2) // with each element
			{
				$apiActionName = $k2; // first element name is action name!
				break 2;
			}
		}
		$apiActionName = array_key_exists($apiActionName,$this->xmlKeyToApiActionMapping)?$this->xmlKeyToApiActionMapping[$apiActionName]:$apiActionName;
		$this->setApiActionName($apiActionName);

		return $this->getApiActionName();
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
