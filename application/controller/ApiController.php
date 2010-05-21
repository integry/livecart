<?php

/**
 * Handles imports through a XML file
 *
 * @package application.api
 * @author Integry Systems
 * 
 */

ClassLoader::import("application.controller.BaseController");
ClassLoader::import("application.model.user.User");
ClassLoader::import("application.model.datasync.XmlApiRequest");
ClassLoader::import("application.helper.datasync.XmlApiResponse");
ClassLoader::import("application.model.datasync.ModelApi");

class ApiController extends BaseController
{
	// where to look for API model classes
	protected $loadModelsFrom = array('application.model.datasync.api');
	
	private $modelApi = null;

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

	private function loadModelApi()
	{
		static $triedToLoadModel = false;
		if(!$triedToLoadModel)
		{
			$request = $this->getRequest();	
			// load found API models and ask if it can parse Request.
			foreach($this->loadModelsFrom as $classLoaderPath)
			{
				$modelFilenames = glob(ClassLoader::getRealPath($classLoaderPath.'.').'*Api.php');
				foreach($modelFilenames as $modelFilename)
				{
					preg_match('/([a-z0-9]+Api)\.php$/i', $modelFilename, $match);
					if(count($match) == 2)
					{
						$modelApiClassName = $match[1];
						ClassLoader::import($classLoaderPath.'.'.$modelApiClassName);
						if(!class_exists($modelApiClassName))
						{
							continue;
						}
						if(call_user_method('canParse', $modelApiClassName, $request))
						{
							$this->setModelApi(new $modelApiClassName($this->application));
							break 2; // stop foreach($modelFilenames..) and foreach($this->loadModelsFrom..
						}
					}
				}
			}
			$triedToLoadModel = true;
		}
		return $this->getModelApi();
	}

	public function xml()
	{
		try {
	
			$model = $this->loadModelApi();
			$apiActionName = $model->getApiActionName();
			if($model->respondsToApiAction($apiActionName))
			{
				//echo '<br />[executing '.$model->getClassName().'Api->'.$apiActionName.'()]<br />';
				return $model->$apiActionName();
				// return $r;
				return new RawResponse('..');
				
			} else {
				throw new Exception('Model '.$model->getClassName().' does not support '.$apiActionName);
			}

		} catch(Exception $e) {
			$xml = new SimpleXMLElement('<response datetime="'.date('c').'"></response>');
			$xml->addChild('error', $e->getMessage());

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
			return new SimpleXMLResponse($xml);
			//return new RawResponse($e->getMessage());
		}
	}		

	public function index()
	{
		try {
			return new RawResponse('index done');
		} catch(Exception $e) {
			return new RawResponse('err');
		}
	}
	
	// @deprecated
	private function userCreate(ApiRequest $request, /*ApiResponse*/ $response)
	{
		$u = $request->getUserData();
		$user = User::getInstanceByEmail($u['email']);
		if($user != null)
		{
			throw new Exception('User exists');
		}
		$user = User::getNewInstance($u['email']);
		$user->firstName->set($u['firstName']);
		$user->lastName->set($u['lastName']);
		$user->companyName->set($u['companyName']);
		// $user->isEnabled->set(TRUE);
		$user->save();
		$response->addChild('created', $user->getID());
		return new SimpleXMLResponse($response);
	}

}

?>
