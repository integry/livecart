<?php

ClassLoader::import("application.model.datasync.ModelAPIDescriptor");
ClassLoader::import("application.model.ActiveRecordModel");

abstract class ModelApi
{
	// @deprecated
	const HANDLE = 0;
	// @deprecated
	const CONDITION = 1;
	// @deprecated
	const ALL_KEYS = -1;
	
	private $className;
	private $apiActionName = null;
	private $parserClassName;
	private $parser;

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
}
