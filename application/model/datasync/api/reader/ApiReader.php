<?php

/**
 * Base for API request parsing (reading/routing)
 *
 * @package application.model.datasync
 * @author Integry Systems <http://integry.com>
 */

abstract class ApiReader implements Iterator
{
	const API_PARSER_DATA = '__api_reader_parser_data_key__';
	const API_PARSER_CLASS_NAME = '__api_reader_parser_class_name_key__';
	const AR_FIELD_HANDLE = 0;
	const AR_CONDITION = 1;
	const ALL_KEYS = -1;

	protected $iteratorKey = 0;
	protected $content;
	
	protected $xmlKeyToApiActionMapping = array(); //? deprecated
	protected $extraFilteringMapping = array();
	protected $apiFields;
		
	private $apiActionName;
	private $fieldNames = array();

	public function __construct($xml, $fieldNames)
	{
		$this->xml = $xml; // $this->setDataResource(); // or smth.
		
		$this->setApiFieldNames($fieldNames);
		$this->findApiActionName($xml);
	}

	public function setApiFields($fieldNames)
	{
		$this->apiFields = $fieldNames;
	}
	
	public function getApiFields()
	{
		return $this->apiFields;
	}

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
	
	
	public function getARSelectFilter($ormClassName)
	{
		$ormFieldNames = $this->getApiFieldNames();
		foreach($ormFieldNames as $fieldName)
		{
			$list[$fieldName] = array(
				self::AR_FIELD_HANDLE => new ARFieldHandle($ormClassName, $fieldName),
				self::AR_CONDITION => 'LikeCond'
			);
		}
		$list = array_merge($list, $this->getExtraFilteringMapping());
		$arsf = new ARSelectFilter();
		$filterKeys = array_keys($list);
		
		foreach(array('//filter/', '//list/') as $xpathPrefix) // todo: pass in xpath prefixes
		{
			foreach($filterKeys as $key)
			{
				$data = $this->xml->xpath($xpathPrefix.$key);
				while(count($data) > 0)
				{
					$value = (string)array_shift($data);
					$arsf->mergeCondition(
						new $list[$key][self::AR_CONDITION](
							$list[$key][self::AR_FIELD_HANDLE],						
							$this->sanitizeFilterField($key, $value)
						)
					);
				}
			}
		}
		return $arsf;
	}
	

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

	abstract protected function findApiActionName($dataHandler);

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

	public function loadDataInRequest($request, $xpathPrefix=null, $fieldNames=null)
	{
		if(func_num_args() < 3)
		{
			throw new Exception('ApiReader->loadDataInRequest(): please implement loadDataInRequest() in your ApiReader (parser) or pass 3 arguments');
		}

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


	public function populate($updater, $profile, $xml, $xpathTemplate, $identificatorFieldNames = array())
	{
		foreach($identificatorFieldNames as $fieldName)
		{
			$item[$fieldName] = null; //?
		}
		$apiActionName = $this->getApiActionName();
		foreach ($updater->getFields() as $group => $fields)
		{
			foreach ($fields as $field => $name)
			{
				list($class, $fieldName) = explode('.', $field);
				if ($class != $profile->getClassName())
				{
					$fieldName = $class . '_' . $fieldName;
				}
				$xpath = str_replace(
					array('[[API_ACTION_NAME]]','[[API_FIELD_NAME]]'),
					array($apiActionName, $fieldName),
					$xpathTemplate
				);
				$v = $xml->xpath($xpath);
				if(count($v) > 0)
				{
					$item[$fieldName] = (string)$v[0];
					$profile->setField($fieldName, $field);
				}
			}
		}
		
		if(count($identificatorFieldNames) == 0)
		{
			$this->addItem($item);
		} else {
			foreach($identificatorFieldNames as $fieldName)
			{
				if($item[$fieldName] != null)
				{
					$this->addItem($item);
					break;
				}
			}
		}
	}
	
	protected function sanitizeFilterField($field, &$value)
	{
		return $value;
	}
	
	protected function getExtraFilteringMapping()
	{
		return array();
	}

	protected static function canParseXml(Request $request, $lookForXpath, $parserClassName)
	{
		$requestData = $request->getRawRequest();

		if(array_key_exists('xml',$requestData))
		{
			$xml = self::getSanitizedSimpleXml($requestData['xml']);
			if($xml != null)
			{
				if(count($xml->xpath($lookForXpath)) == 1)
				{
					$request->set(ApiReader::API_PARSER_DATA ,$xml);
					$request->set(ApiReader::API_PARSER_CLASS_NAME, $parserClassName);
					return true;
				}
			}
		}
	}
	
}

?>