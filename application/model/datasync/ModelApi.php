<?php

ClassLoader::import("application.model.ActiveRecordModel");

abstract class ModelApi
{
	private $className;
	private $parserClassName;
	private $parser;

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
	
	protected function __construct(LiveCart $application, $className, $fieldNames=array())
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
		$this->setParser(new $cn($request->get('_ApiParserData'), $fieldNames));
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


}
