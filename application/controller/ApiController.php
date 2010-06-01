<?php

/**
 * Handles imports through a XML file
 *
 * @package application.api
 * @author Integry Systems
 * 
 */

ClassLoader::import('application.controller.BaseController');
ClassLoader::import('application.model.user.User');
ClassLoader::import('application.model.datasync.XmlApiRequest');
ClassLoader::import('application.helper.datasync.XmlApiResponse');
ClassLoader::import('application.model.datasync.ModelApi');

class ApiController extends BaseController
{
	// where to look for API model classes
	protected $loadModelsFrom = array('application.model.datasync.api');
	
	private $modelApi = null;

	public function index()
	{

	}

	public function xml()
	{
		try {	
			$model = $this->loadModelApi();
			$apiActionName = $model->getApiActionName();
			if($model->respondsToApiAction($apiActionName))
			{
				if (0 && !$model->isAuthorized())
				{
					throw new Exception('Unauthorized');
				}
				
				//echo '<br />[executing '.$model->getClassName().'Api->'.$apiActionName.'()]<br />';
				return $model->$apiActionName();
			} else {
				if($apiActionName == '')
				{
					throw new Exception('Failed to detect model API action name');
				} else {
					throw new Exception('Model '.$model->getClassName().' does not support '.$apiActionName);
				}
			}

		} catch(Exception $e) {
			$xml = new SimpleXMLElement('<response datetime="'.date('c').'"></response>');
			$xml->addChild('error', $e->getMessage());
			if($this->application->isDevMode())
			{
				$xmlTrace = $xml->addChild('trace');
				foreach($e->getTrace() as $row)
				{
					$line = array();
					if(isset($row['line']))
					{
						$line[] = '['.$row['line'].']';
					} else {
						$line[] = '[ - ]';
					}

					if(isset($row['file']))
					{
						$line[] = '['.$row['file'].']';
					} else {
						$line[] = '[ - ]';
					}

					$line[] = ' [';
					if(isset($row['class']))
					{
						$line[] = $row['class'].'::';
					}
					if(isset($row['function']))
					{
						$line[] = $row['function'].'(..)';
					}
					$line[] = ']';
					$xmlTrace->addChild('r',  implode(' ',$line));	
				}
			}
			return new SimpleXMLResponse($xml);
			//return new RawResponse($e->getMessage());
		}
	}		
	
	public function doc()
	{
		$classes = $this->getApiClasses();
		$response = new ActionResponse('classes', $classes);
		
		return $response;		
	}

	private function setModelApi(ModelApi $clazz)
	{
		$this->modelApi = $clazz;
	}

	private function getModelApi()
	{
		if($this->modelApi == null)
		{
			throw new Exception('Cannot find Model to parse request'); // Cannot parse request
		}
		return $this->modelApi;
	}
	
	private function getApiClasses()
	{
		$classes = array();
		foreach($this->loadModelsFrom as $classLoaderPath)
		{
			$modelFilenames = glob(ClassLoader::getRealPath($classLoaderPath.'.').'*Api.php');

			foreach($modelFilenames as $modelFilename)
			{
				preg_match('/([a-z0-9]+Api)\.php$/i', $modelFilename, $match);
				if(count($match) == 2)
				{
					$classes[$match[1]] = $modelFilename;
				}
			}
		}
		
		return $classes;
	}

	private function loadModelApi()
	{
		static $triedToLoadModel = false;
		if(!$triedToLoadModel)
		{
			$request = $this->getRequest();	
			foreach ($this->getApiClasses() as $modelApiClassName => $path)
			{
				include_once $path;
				
				if (!class_exists($modelApiClassName, false))
				{
					continue;
				}
				
				if (call_user_func_array(array($modelApiClassName, "canParse"), array($request)))
				{
					$this->setModelApi(new $modelApiClassName($this->application));
					break;
				}
			}
			$triedToLoadModel = true;
		}
		return $this->getModelApi();
	}
}

?>
