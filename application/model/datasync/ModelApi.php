<?php

ClassLoader::import("application.model.datasync.ModelAPIDescriptor");
ClassLoader::import("application.model.ActiveRecordModel");

abstract class ModelApi
{
	private $className;
	private $apiActionName = null;
	private $parserClassName;
	private $parser;

	protected function __construct(LiveCart $application, $className)
	{
		$this->application = $application;
		$this->className = $className;

		$request = $this->application->getRequest();
		$cn = $request->get('_ApiParserClassName');
		if($cn == null || class_exists($cn) == false)
		{
			throw new Exception('Parser '.$cn.' not found');
		}
		$this->setParserClassName($cn);
		$this->setParser(new $cn($request->get('_ApiParserData')));
	}

	abstract static function canParse(Request $request);
	
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
	
	public function filter() // because list() is keyword.
	{
		$this->beforeFilter();
		// ..
		$this->afterFilter();		
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

	protected function beforeFilter()
	{
	}

	protected function afterFilter()
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
	
	public function getApiActionName()
	{
		return $this->getParser()->getApiActionName();
	}

}
