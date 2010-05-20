<?php

ClassLoader::import("application.model.datasync.ModelAPIDescriptor");
ClassLoader::import("application.model.ActiveRecordModel");

abstract class ModelApi
{
	const HANDLE = 0;
	const CONDITION = 1;
	const ALL_KEYS = -1;
	private $className;
	private $apiActionName = null;
	
	public static function canParse(Request $request)
	{
		return false; // this is abstract 'API thing', can't parse anything.
	}

	public function __construct($className)
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

	public function getApiActionName()
	{
		return $this->apiActionName;
	}
	
	public function setApiActionName($apiActionName)
	{
		$this->apiActionName=$apiActionName;
	}

	public function respondsToApiAction($apiAction)
	{
		return in_array($apiAction, $this->getActions());
	}

	public function getListFilterConditionAndARHandle($key)
	{
		$mapping = $this->getListFilterMapping();
		if(array_key_exists($key, $mapping) == false || array_key_exists(self::CONDITION, $mapping[$key]) == false)
		{
			throw new Exception('Condition for key ['.$key.'] not found in mapping');
		}
		if(array_key_exists($key, $mapping) == false || array_key_exists(self::HANDLE, $mapping[$key]) == false)
		{
			throw new Exception('Handle for key ['.$key.'] not found in mapping');
		}

		return $mapping[$key];
	}
	
	public function getListFilterCondition($key)
	{
		$r = $this->getListFilterConditionAndARHandle($key);
		return $r[$key][self::CONDITION];
	}
	
	public function getListFilterARHandle($key)
	{
		$r = $this->getListFilterConditionAndARHandle($key);
		return $r[$key][self::HANDLE];
	}

	public function getListFilterKeys()
	{
		return array_keys($this->getListFilterMapping());
	}

	public function update()
	{
		$this->beforeUpdate();
		// ..
		$this->afterUpdate();
	}

	public function create()
	{
		$this->beforeCreate();
		// ..
		$this->afterCreate();
	}
	
	public function delete()
	{
		$this->beforeDelete();
		// ..
		$this->afterDelete();
	}
	
	public function get()
	{
		$this->beforeGet();
		// ..
		$this->afterGet();
	}
	
	public function listItems() // because list() is keyword.
	{
		$this->beforeListItems();
		// ..
		$this->afterListItems();		
	}
	
	protected function beforeUpdate()
	{
	}

	protected function afterUpdate()
	{
	}

	protected function beforeCreate()
	{
	}

	protected function afterCreate()
	{
	}

	protected function beforeDelete()
	{
	}

	protected function afterDelete()
	{
	}

	protected function beforeGet()
	{
	}

	protected function afterGet()
	{
	}

	protected function beforeListItems()
	{
	}

	protected function afterListItems()
	{
	}

	protected function getSanitizedSimpleXml($xmlString)
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
}
