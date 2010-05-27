<?php

ClassLoader::import('application.model.ActiveRecordModel');

/**
 * Web service access layer model base
 *
 * @package application.model.datasync
 * @author Integry Systems <http://integry.com>
 * 
 */

abstract class ModelApi
{
	public $className;
	public $parserClassName;
	public $parser;	
	public $importedIDs = array(); // used in models that are using csv 'importers'

	public static function canParse(Request $request, $parserClassNames=array())
	{
		foreach($parserClassNames as $parserClassName)
		{
			ClassLoader::import('application.model.datasync.api.reader.'.$parserClassName);
			if(call_user_func_array(array($parserClassName, "canParse"), array($request)))
			{
				return true;
			}
		}
		return false;
	}

	protected function __construct(LiveCart $application, $className, $ignoreFieldNames = array())
	{
		$this->setClassName($className);
		$this->setApplication($application);

		$request = $this->getApplication()->getRequest();
		$cn = $request->get(ApiReader::API_PARSER_CLASS_NAME);
		if($cn == null || class_exists($cn) == false)
		{
			throw new Exception('Parser '.$cn.' not found');
		}
		$modelFields = ActiveRecordModel::getSchemaInstance($className)->getFieldList();
		// $modelFieldNames - $ignoreFieldNames
		$modelFieldNames = array_diff(array_keys($modelFields),is_array($ignoreFieldNames)?$ignoreFieldNames:array($ignoreFieldNames));

		$this->setParserClassName($cn);
		$this->setParser(new $cn($request->get(ApiReader::API_PARSER_DATA), $modelFieldNames));

		// read and put data from api request format (that could be wahatever custom parser can read) in  Requst object as key=>value pairs.
		$this->getParser()->loadDataInRequest($this->getApplication()->getRequest());
	}

	public function setApplication($application)
	{
		$this->application = $application;
	}
	
	public function getApplication()
	{
		return $this->application;
	}
	
	public function setClassName($className)
	{
		$this->className = $className;
	}
	
	public function getClassName()
	{
		return $this->className;
	}

	public function getDefaultActions()
	{
		return array('create', 'filter', 'update', 'delete', 'get');
	}

	public function getActions()
	{
		return $this->getDefaultActions();
	}

	public function respondsToApiAction($apiAction)
	{
		return in_array($apiAction, $this->getActions());
	}

	public function update()
	{
		throw new Exception('Action not implement');
	}

	public function create()
	{
		throw new Exception('Action not implement');
	}
	
	public function delete()
	{
		throw new Exception('Action not implement');
	}
	
	public function get()
	{
		throw new Exception('Action not implement');
	}
	
	public function filter() // because list() is keyword.
	{
		throw new Exception('Action not implement');
	}
	
	protected function setParserClassName($className)
	{
		$this->parserClassName = $className;
	}
	
	public function getParserClassName()
	{
		return $this->parserClassName;
	}
	
	protected function setParser($parser)
	{
		$this->parser = $parser;
	}

	public function getParser()
	{
		return $this->parser;
	}
	
	public function getApiActionName()
	{
		return $this->getParser()->getApiActionName();
	}
	
	protected function statusResponse($ids, $status)
	{
		$response = new SimpleXMLElement('<response datetime="'.date('c').'"></response>');
		if(is_array($ids) == false)
		{
			$ids = array($ids);
		}
		foreach($ids as $id)
		{
			$response->addChild($status, $id);
		}
		return new SimpleXMLResponse($response);
	}

	protected function getRequestID()
	{
		$request = $this->getApplication()->getRequest();
		$id = $request->get('ID');
		if(false == is_numeric($id))
		{
			$id = $request->get('id');
		}
		if(false == is_numeric($id))
		{
			throw new Exception('Bad ID field value.');
		}
		return $id;
	}

	//
	// todo:
	//    * detect 'cyclic' recursions
	//    * some sort of filtering mechanism
	public function mapToSimpleXMLElement(SimpleXMLElement $xml, $array, $prefix = '')
	{
		if(is_array($array) == false)
		{
			return $xml;
		}
		foreach($array as $key=>$value)
		{
			if($prefix)
			{
				$key = $prefix.'_'.$key;
			}

			// cast value to string
			if(is_object($value))
			{
				if(method_exists($value, 'toArray'))
				{
					$value = $value->toArray();
				}
				elseif (method_exists($value, '__toArray'))
				{
					$value = $value->__toArray();
				}
				elseif(method_exists($value, '__toString'))
				{
					$value = (string)$value;
				}
			}
			elseif (is_bool($value))
			{
				$value = $value ? 'TRUE' : 'FALSE';
			}
			elseif (is_numeric($value))
			{
				$value = (string)$value;
			}

			// value was cast to string
			if(is_string($value))
			{
				$xml->addChild($key, $value);
			} else {
				$xml = self::mapToSimpleXMLElement($xml, $value, $key);
			}
		}
		return $xml;
	}
}

?>
